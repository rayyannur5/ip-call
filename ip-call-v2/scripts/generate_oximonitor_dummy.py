#!/usr/bin/env python3
"""
Generate dummy oximonitor_log data.

Default behavior:
- last 60 calendar days, including today
- one row every 5 minutes
- cumulative volume with an average usage of 200 m3 per day
- writes batched SQL inserts to storage/app/oximonitor_dummy.sql

Examples:
  python3 scripts/generate_oximonitor_dummy.py
  python3 scripts/generate_oximonitor_dummy.py --days 60 --daily-average 200
  python3 scripts/generate_oximonitor_dummy.py --execute --truncate
"""

from __future__ import annotations

import argparse
import datetime as dt
import math
import os
import random
import shlex
import subprocess
from pathlib import Path


INTERVAL_MINUTES = 5
ROWS_PER_DAY = 24 * 60 // INTERVAL_MINUTES


def parse_env(path: Path) -> dict[str, str]:
    values: dict[str, str] = {}
    if not path.exists():
        return values

    for raw_line in path.read_text().splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue

        key, value = line.split("=", 1)
        value = value.strip()
        if len(value) >= 2 and value[0] == value[-1] and value[0] in {"'", '"'}:
            value = value[1:-1]
        values[key.strip()] = value

    return values


def decimal_comma(value: float) -> str:
    return f"{value:.6f}".rstrip("0").rstrip(".")


def daily_totals(days: int, average: float, rng: random.Random) -> list[float]:
    totals = [average * rng.uniform(0.88, 1.12) for _ in range(days)]
    target = days * average
    current = sum(totals)
    if current == 0:
        return [average] * days

    return [total * target / current for total in totals]


def interval_usage(day_total: float, slot: int, rng: random.Random) -> float:
    hour = (slot * INTERVAL_MINUTES) / 60
    morning_peak = math.exp(-((hour - 8.0) ** 2) / 18)
    afternoon_peak = math.exp(-((hour - 15.0) ** 2) / 24)
    night_floor = 0.55 if hour < 5 or hour > 22 else 1.0
    shape = (0.55 + 0.45 * morning_peak + 0.35 * afternoon_peak) * night_floor
    noise = rng.uniform(0.85, 1.15)
    base = day_total / ROWS_PER_DAY

    return base * shape * noise


def generate_rows(
    start_date: dt.date,
    days: int,
    average: float,
    initial_volume: float,
    seed: int,
) -> list[tuple[float, dt.datetime]]:
    rng = random.Random(seed)
    totals = daily_totals(days, average, rng)
    rows: list[tuple[float, dt.datetime]] = []
    volume = initial_volume

    for day_index, day_total in enumerate(totals):
        date = start_date + dt.timedelta(days=day_index)
        raw_usages = [interval_usage(day_total, slot, rng) for slot in range(ROWS_PER_DAY)]
        scale = day_total / sum(raw_usages)

        for slot, raw_usage in enumerate(raw_usages):
            timestamp = dt.datetime.combine(date, dt.time()) + dt.timedelta(
                minutes=slot * INTERVAL_MINUTES
            )
            volume += raw_usage * scale
            rows.append((round(volume, 6), timestamp))

    return rows


def sql_header(args: argparse.Namespace, rows: list[tuple[float, dt.datetime]]) -> str:
    first_date = rows[0][1].strftime("%Y-%m-%d %H:%M:%S") if rows else "-"
    last_date = rows[-1][1].strftime("%Y-%m-%d %H:%M:%S") if rows else "-"
    return "\n".join(
        [
            "-- Dummy data for oximonitor_log",
            f"-- Generated rows: {len(rows)}",
            f"-- Range: {first_date} to {last_date}",
            f"-- Interval: {INTERVAL_MINUTES} minutes",
            f"-- Daily average: {decimal_comma(args.daily_average)} m3",
            f"-- Seed: {args.seed}",
            "",
        ]
    )


def write_sql_file(
    output: Path,
    rows: list[tuple[float, dt.datetime]],
    args: argparse.Namespace,
) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)

    with output.open("w", encoding="utf-8") as handle:
        handle.write(sql_header(args, rows))

        if args.truncate:
            handle.write("TRUNCATE TABLE oximonitor_log;\n\n")

        for index in range(0, len(rows), args.batch_size):
            batch = rows[index : index + args.batch_size]
            handle.write("INSERT INTO oximonitor_log (volume, created_at) VALUES\n")
            values = []
            for volume, created_at in batch:
                timestamp = created_at.strftime("%Y-%m-%d %H:%M:%S")
                values.append(f"({decimal_comma(volume)}, '{timestamp}')")
            handle.write(",\n".join(values))
            handle.write(";\n\n")


def mysql_command(env: dict[str, str], sql_file: Path) -> list[str]:
    command = [
        "mysql",
        "-h",
        env.get("DB_HOST", "localhost"),
        "-P",
        env.get("DB_PORT", "3306"),
        "-u",
        env.get("DB_USERNAME", "root"),
    ]

    password = env.get("DB_PASSWORD")
    if password not in (None, ""):
        command.append(f"-p{password}")

    command.extend([env.get("DB_DATABASE", ""), "-e", f"source {sql_file}"])
    return command


def execute_sql(env_path: Path, sql_file: Path) -> None:
    env = parse_env(env_path)
    database = env.get("DB_DATABASE")
    if not database:
        raise SystemExit(f"DB_DATABASE not found in {env_path}")

    command = mysql_command(env, sql_file)
    display = " ".join(shlex.quote(part) for part in command)
    print(f"Executing: {display}")
    subprocess.run(command, check=True)


def parse_args() -> argparse.Namespace:
    project_root = Path(__file__).resolve().parents[1]
    default_output = project_root / "storage" / "app" / "oximonitor_dummy.sql"

    parser = argparse.ArgumentParser(
        description="Generate dummy cumulative oximonitor_log rows."
    )
    parser.add_argument("--days", type=int, default=60, help="Number of calendar days.")
    parser.add_argument(
        "--daily-average",
        type=float,
        default=200.0,
        help="Average usage per day in m3.",
    )
    parser.add_argument(
        "--start-date",
        type=str,
        help="Start date in YYYY-MM-DD. Default: today - days + 1.",
    )
    parser.add_argument(
        "--initial-volume",
        type=float,
        default=0.0,
        help="Starting cumulative volume before the first generated row.",
    )
    parser.add_argument("--seed", type=int, default=20260613, help="Random seed.")
    parser.add_argument(
        "--batch-size",
        type=int,
        default=500,
        help="Rows per INSERT statement.",
    )
    parser.add_argument(
        "--output",
        type=Path,
        default=default_output,
        help="SQL output file path.",
    )
    parser.add_argument(
        "--truncate",
        action="store_true",
        help="Add TRUNCATE TABLE oximonitor_log before inserts.",
    )
    parser.add_argument(
        "--execute",
        action="store_true",
        help="Execute the generated SQL using mysql CLI and .env DB settings.",
    )
    parser.add_argument(
        "--env",
        type=Path,
        default=project_root / ".env",
        help="Laravel .env path for --execute.",
    )

    args = parser.parse_args()
    if args.days <= 0:
        raise SystemExit("--days must be greater than 0")
    if args.daily_average <= 0:
        raise SystemExit("--daily-average must be greater than 0")
    if args.batch_size <= 0:
        raise SystemExit("--batch-size must be greater than 0")

    return args


def main() -> None:
    args = parse_args()

    if args.start_date:
        start_date = dt.date.fromisoformat(args.start_date)
    else:
        today = dt.datetime.now().date()
        start_date = today - dt.timedelta(days=args.days - 1)

    rows = generate_rows(
        start_date=start_date,
        days=args.days,
        average=args.daily_average,
        initial_volume=args.initial_volume,
        seed=args.seed,
    )
    total_usage = rows[-1][0] - args.initial_volume if rows else 0
    actual_average = total_usage / args.days if args.days else 0

    write_sql_file(args.output, rows, args)

    print(f"Generated {len(rows)} rows")
    print(
        "Range "
        f"{rows[0][1].strftime('%Y-%m-%d %H:%M:%S')} "
        f"to {rows[-1][1].strftime('%Y-%m-%d %H:%M:%S')}"
    )
    print(f"Total usage: {decimal_comma(total_usage)} m3")
    print(f"Daily average: {decimal_comma(actual_average)} m3")
    print(f"SQL file: {args.output}")

    if args.execute:
        execute_sql(args.env, args.output)


if __name__ == "__main__":
    main()
