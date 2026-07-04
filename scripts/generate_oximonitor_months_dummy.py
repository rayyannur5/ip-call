#!/usr/bin/env python3
"""
Generate dummy oximonitor_log data for a month range.

Default behavior:
- January 2026 through July 2026
- one row every 5 minutes
- cumulative volume with an average usage of 200 m3 per day
- writes batched SQL inserts to storage/app/oximonitor_dummy_jan_jul_2026.sql

Examples:
  python3 scripts/generate_oximonitor_months_dummy.py
  python3 scripts/generate_oximonitor_months_dummy.py --from-month 2026-01 --to-month 2026-07
  python3 scripts/generate_oximonitor_months_dummy.py --execute --truncate
"""

from __future__ import annotations

import argparse
import calendar
import datetime as dt
from pathlib import Path

from generate_oximonitor_dummy import (
    INTERVAL_MINUTES,
    decimal_comma,
    execute_sql,
    generate_rows,
    write_sql_file,
)


def parse_month(value: str) -> tuple[int, int]:
    try:
        parsed = dt.datetime.strptime(value, "%Y-%m")
    except ValueError as exc:
        raise argparse.ArgumentTypeError("month must use YYYY-MM format") from exc

    return parsed.year, parsed.month


def month_range(from_month: tuple[int, int], to_month: tuple[int, int]) -> tuple[dt.date, dt.date]:
    from_year, from_month_number = from_month
    to_year, to_month_number = to_month

    start_date = dt.date(from_year, from_month_number, 1)
    last_day = calendar.monthrange(to_year, to_month_number)[1]
    end_date = dt.date(to_year, to_month_number, last_day)

    if start_date > end_date:
        raise SystemExit("--from-month must be earlier than or equal to --to-month")

    return start_date, end_date


def parse_args() -> argparse.Namespace:
    project_root = Path(__file__).resolve().parents[1]
    default_output = project_root / "storage" / "app" / "oximonitor_dummy_jan_jul_2026.sql"

    parser = argparse.ArgumentParser(
        description="Generate dummy cumulative oximonitor_log rows for whole months."
    )
    parser.add_argument(
        "--from-month",
        type=parse_month,
        default=parse_month("2026-01"),
        help="First month in YYYY-MM format.",
    )
    parser.add_argument(
        "--to-month",
        type=parse_month,
        default=parse_month("2026-07"),
        help="Last month in YYYY-MM format.",
    )
    parser.add_argument(
        "--daily-average",
        type=float,
        default=200.0,
        help="Average usage per day in m3.",
    )
    parser.add_argument(
        "--initial-volume",
        type=float,
        default=0.0,
        help="Starting cumulative volume before the first generated row.",
    )
    parser.add_argument("--seed", type=int, default=20260701, help="Random seed.")
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
    if args.daily_average <= 0:
        raise SystemExit("--daily-average must be greater than 0")
    if args.batch_size <= 0:
        raise SystemExit("--batch-size must be greater than 0")

    args.start_date, args.end_date = month_range(args.from_month, args.to_month)
    args.days = (args.end_date - args.start_date).days + 1

    return args


def main() -> None:
    args = parse_args()
    rows = generate_rows(
        start_date=args.start_date,
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
    print(f"Interval: {INTERVAL_MINUTES} minutes")
    print(f"Days: {args.days}")
    print(f"Total usage: {decimal_comma(total_usage)} m3")
    print(f"Daily average: {decimal_comma(actual_average)} m3")
    print(f"SQL file: {args.output}")

    if args.execute:
        execute_sql(args.env, args.output)


if __name__ == "__main__":
    main()
