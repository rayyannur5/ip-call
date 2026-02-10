"""
Liquidsoap Radio Scheduler
===========================
Mengelola penjadwalan audio (musik & adzan), proses Liquidsoap,
dan status MQTT secara periodik.

Struktur:
  - log_message()         : Logging dengan timestamp
  - ServerAPI             : Pengambilan data dari server PHP
  - AudioScheduler        : Penjadwalan musik & adzan, flag state audio
  - LiquidsoapManager     : Manajemen proses Liquidsoap & file .liq
  - main()                : Entry point & main loop
"""

import os
import re
import time
import json
import queue
import argparse
import subprocess
import threading

import schedule
import requests
import paho.mqtt.client as mqtt
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
from datetime import date, datetime, time as dt_time, timedelta
from pyIslam.praytimes import PrayerConf, Prayer

# ===========================================================================
#  Konstanta
# ===========================================================================

DEFAULT_LATITUDE = -7.288354699999995
DEFAULT_LONGITUDE = 112.72549628465647
DEFAULT_LIQ_FILE_PATH = "/home/nursecallserver/ip-call/liquidsoap/radio.liq"

MUSIC_SCHEDULE_URL = "http://localhost/ip-call/server/music.php"
UTILS_URL = "http://localhost/ip-call/server/utils.php"
ADZAN_SCHEDULE_URL = "http://localhost/ip-call/server/adzan.php"

MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC_AUDIO = "schedule_audio"
MQTT_CLIENT_ID = f"liquidsoap_scheduler_{os.getpid()}"

MQTT_POLL_INTERVAL = 30  # detik
ADZAN_WINDOW_MINUTES = 5

PRAYER_KEY_MAP = {
    "subuh": "Fajr",
    "dhuhur": "Dhuhr",
    "ashar": "Asr",
    "maghrib": "Maghrib",
    "isya": "Isha",
}


# ===========================================================================
#  Logging
# ===========================================================================

def log_message(message):
    """Mencetak pesan dengan timestamp. Pesan PY_DEBUG diabaikan."""
    if message.startswith("PY_DEBUG:"):
        return
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S.%f")[:-3]
    print(f"[{timestamp}] {message}")


# ===========================================================================
#  MQTT
# ===========================================================================

def publish_mqtt(topic, payload):
    """Mempublikasikan pesan MQTT dengan QoS 1 dan Retain True."""
    try:
        client = mqtt.Client()
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
        result = client.publish(topic, payload, qos=1, retain=True)

        if result.rc == mqtt.MQTT_ERR_SUCCESS:
            log_message(
                f"MQTT: Pesan '{payload}' berhasil dikirim "
                f"ke topik '{topic}'. MID: {result.mid}"
            )
        else:
            log_message(
                f"MQTT: Gagal mengirim pesan ke topik '{topic}', "
                f"kode status: {result.rc}"
            )

        client.loop_stop()
        client.disconnect()
    except Exception as e:
        log_message(f"MQTT: Error saat publikasi - {e}")


# ===========================================================================
#  ServerAPI — Pengambilan data dari server PHP
# ===========================================================================

class ServerAPI:
    """Mengambil data konfigurasi, lokasi, dan waktu sholat dari server."""

    def __init__(self):
        self.latitude = DEFAULT_LATITUDE
        self.longitude = DEFAULT_LONGITUDE

    # --- Utils ---

    def get_utils_data(self):
        """Mengambil list data dari utils.php. Return list atau None."""
        try:
            resp = requests.get(UTILS_URL, timeout=5)
            resp.raise_for_status()
            data = resp.json()
            if data.get("success") and "data" in data:
                return data["data"]
            log_message("UTILS_DATA: Respons tidak sukses atau format tidak sesuai.")
        except requests.exceptions.RequestException as e:
            log_message(f"UTILS_DATA: Error request: {e}.")
        except json.JSONDecodeError as e:
            log_message(f"UTILS_DATA: Error parsing JSON: {e}.")
        except Exception as e:
            log_message(f"UTILS_DATA: Error tidak terduga: {e}.")
        return None

    def _find_utils_value(self, type_key, default=None):
        """Cari satu nilai dari utils berdasarkan type_key."""
        utils_list = self.get_utils_data()
        if utils_list:
            for item in utils_list:
                if item.get("type") == type_key:
                    return item.get("value", default)
        return default

    # --- Lokasi ---

    def update_location(self):
        """Perbarui latitude & longitude dari server."""
        log_message("LOCATION: Mencoba memperbarui koordinat dari server...")
        utils_list = self.get_utils_data()

        if not utils_list:
            log_message("LOCATION: Gagal mengambil data utils. Koordinat tidak diperbarui.")
            self._log_current_location()
            return

        new_lat, new_lon = None, None
        for item in utils_list:
            item_type = item.get("type")
            item_value = item.get("value")

            if item_type == "adzan_latitude":
                try:
                    new_lat = float(item_value)
                    log_message(f"LOCATION: Latitude dari server: {new_lat}")
                except (ValueError, TypeError):
                    log_message(f"LOCATION: Nilai latitude tidak valid: {item_value}")

            elif item_type == "adzan_longitude":
                try:
                    new_lon = float(item_value)
                    log_message(f"LOCATION: Longitude dari server: {new_lon}")
                except (ValueError, TypeError):
                    log_message(f"LOCATION: Nilai longitude tidak valid: {item_value}")

        if new_lat is not None:
            self.latitude = new_lat
        if new_lon is not None:
            self.longitude = new_lon

        self._log_current_location()

    def _log_current_location(self):
        log_message(
            f"LOCATION: Koordinat saat ini: "
            f"Latitude={self.latitude}, Longitude={self.longitude}"
        )

    # --- Adzan Config ---

    def get_adzan_active_status(self):
        """Return '1' (aktif) atau '0' (nonaktif)."""
        utils_list = self.get_utils_data()
        if utils_list:
            for item in utils_list:
                if item.get("type") == "adzan_active":
                    return item.get("value", "1")
            log_message("ADZAN_STATUS: Tipe 'adzan_active' tidak ditemukan. Default '1'.")
        else:
            log_message("ADZAN_STATUS: Gagal mengambil data utils. Default '1'.")
        return "1"

    def get_adzan_amplify(self):
        """Return faktor amplifikasi adzan (float 0.0-1.0)."""
        default = 1.0
        utils_list = self.get_utils_data()
        if utils_list:
            for item in utils_list:
                if item.get("type") == "adzan_volume":
                    try:
                        return float(item.get("value")) / 100.0
                    except (ValueError, TypeError):
                        log_message("AMPLIFY: Nilai volume tidak valid. Default.")
                        return default
            log_message("AMPLIFY: Tipe 'adzan_volume' tidak ditemukan. Default.")
        else:
            log_message("AMPLIFY: Gagal mengambil data utils. Default.")
        return default

    # --- Prayer Times ---

    def get_prayer_times(self):
        """
        Ambil waktu sholat. Return dict {"Fajr": time, "Dhuhr": time, ...}.
        Prioritas: manual (adzan.php) jika adzan_auto=0, lalu pyIslam.
        """
        log_message("PRAYER_TIMES: Memulai pengambilan data waktu sholat.")

        # Cek mode manual/otomatis
        adzan_auto = self._find_utils_value("adzan_auto", "1")

        if adzan_auto == "0":
            manual_map = self._fetch_manual_prayer_times()
            if manual_map:
                return manual_map
            log_message("PRAYER_TIMES: Fallback ke pyIslam.")

        return self._calc_prayer_times()

    def _fetch_manual_prayer_times(self):
        """Ambil waktu sholat dari adzan.php. Return dict atau None."""
        log_message("PRAYER_TIMES: adzan_auto=0, mengambil dari adzan.php.")
        try:
            resp = requests.get(ADZAN_SCHEDULE_URL, timeout=5)
            resp.raise_for_status()
            schedules = resp.json()

            prayer_map = {}
            valid_count = 0
            for item in schedules:
                prayer_name = PRAYER_KEY_MAP.get(item.get("key"))
                if prayer_name and item.get("value"):
                    try:
                        prayer_map[prayer_name] = dt_time.fromisoformat(item["value"])
                        valid_count += 1
                    except ValueError:
                        log_message(f"PRAYER_TIMES: Format waktu manual tidak valid: {item}")

            if valid_count == 5:
                log_message("PRAYER_TIMES: Berhasil mengambil waktu manual.")
                return prayer_map

            log_message(f"PRAYER_TIMES: Waktu manual tidak lengkap ({valid_count}/5). Fallback.")
        except Exception as e:
            log_message(f"PRAYER_TIMES: Error jadwal manual: {e}. Fallback.")
        return None

    def _calc_prayer_times(self):
        """Hitung waktu sholat dengan pyIslam."""
        log_message(
            f"PRAYER_TIMES: Menggunakan pyIslam "
            f"Lat: {self.latitude}, Lon: {self.longitude}."
        )
        conf = PrayerConf(self.longitude, self.latitude, 7, 7, 1)
        prayers = Prayer(conf, date.today())
        return {
            "Fajr": prayers.fajr_time(),
            "Dhuhr": prayers.dohr_time(),
            "Asr": prayers.asr_time(),
            "Maghrib": prayers.maghreb_time(),
            "Isha": prayers.ishaa_time(),
        }


# ===========================================================================
#  AudioScheduler — Penjadwalan musik & adzan, flag state audio
# ===========================================================================

class AudioScheduler:
    """Mengelola jadwal musik & adzan, serta flag status audio."""

    def __init__(self, server):
        self.server = server
        self.music_schedules = []     # Cache jadwal musik dari server
        self.is_music_active = False  # Musik seharusnya aktif saat ini?
        self.is_adzan_active = False  # Jendela waktu adzan aktif?

    @property
    def audio_should_be_on(self):
        """True jika musik atau adzan aktif."""
        return self.is_music_active or self.is_adzan_active

    @property
    def audio_payload(self):
        """Return '1' atau '0' untuk MQTT."""
        return "1" if self.audio_should_be_on else "0"

    # --- Musik ---

    def fetch_and_schedule_music(self):
        """Ambil jadwal musik dari server, buat schedule event start/end."""
        log_message("MUSIC_SCHEDULER: Memproses jadwal musik...")

        # Fetch dari server
        try:
            resp = requests.get(MUSIC_SCHEDULE_URL, timeout=10)
            resp.raise_for_status()
            self.music_schedules = resp.json() or []
            log_message(
                f"MUSIC_SCHEDULER: Menerima {len(self.music_schedules)} jadwal musik."
            )
        except Exception as e:
            log_message(f"MUSIC_SCHEDULER: Error mengambil jadwal musik: {e}")
            self.music_schedules = []

        # Bersihkan dan buat jadwal baru
        schedule.clear('music-schedule-event')
        log_message("MUSIC_SCHEDULER: Jadwal event musik lama dibersihkan.")

        for item in self.music_schedules:
            try:
                name = item.get("name", "N/A")
                start_str = item.get("start_time")
                end_str = item.get("end_time")

                if not start_str or not end_str:
                    log_message(f"MUSIC_SCHEDULER: Lewati '{name}', waktu tidak lengkap.")
                    continue

                start_t = dt_time.fromisoformat(start_str)
                end_t = dt_time.fromisoformat(end_str)
                start_hm = start_t.strftime("%H:%M")
                end_hm = end_t.strftime("%H:%M")

                log_message(
                    f"MUSIC_SCHEDULER: Jadwalkan '{name}' "
                    f"@ Start: {start_hm}, End: {end_hm}"
                )

                schedule.every().day.at(start_hm).do(
                    self._on_music_start, name=name
                ).tag('music-schedule-event', f'event-start-{name}')

                schedule.every().day.at(end_hm).do(
                    self._on_music_end, name=name
                ).tag('music-schedule-event', f'event-end-{name}')

            except ValueError:
                log_message(f"MUSIC_SCHEDULER: Format waktu tidak valid untuk '{name}'.")
            except Exception as e:
                log_message(f"MUSIC_SCHEDULER: Error jadwal '{name}': {e}")

        # Update flag musik saat ini
        self._update_music_flag()

        if not self.audio_should_be_on:
            log_message("MUSIC_SCHEDULER: Tidak ada musik/adzan aktif saat ini.")
        elif self.is_music_active:
            log_message("MUSIC_SCHEDULER: Musik terdeteksi aktif saat ini.")

        log_message("MUSIC_SCHEDULER: Pemrosesan jadwal musik selesai.")

    def _update_music_flag(self):
        """Periksa jadwal musik terhadap waktu saat ini, update is_music_active."""
        now_time = datetime.now().time()
        was_active = self.is_music_active
        active_now = False

        if not self.music_schedules:
            log_message("PY_DEBUG: _update_music_flag - Tidak ada jadwal musik.")
            if was_active:
                log_message("MUSIC_STATE: Musik menjadi TIDAK AKTIF (tidak ada jadwal).")
            self.is_music_active = False
            return

        for item in self.music_schedules:
            try:
                start_str = item.get("start_time")
                end_str = item.get("end_time")
                if not start_str or not end_str:
                    continue
                start_t = dt_time.fromisoformat(start_str)
                end_t = dt_time.fromisoformat(end_str)
                if self._time_in_range(now_time, start_t, end_t):
                    active_now = True
                    break
            except (ValueError, Exception) as e:
                log_message(f"PY_DEBUG: _update_music_flag - Error: {e}")
                continue

        if active_now and not was_active:
            log_message("MUSIC_STATE: Musik menjadi AKTIF berdasarkan jadwal.")
        elif not active_now and was_active:
            log_message("MUSIC_STATE: Musik menjadi TIDAK AKTIF berdasarkan jadwal.")

        self.is_music_active = active_now
        log_message(f"PY_DEBUG: _update_music_flag - is_music_active: {self.is_music_active}")

    def _on_music_start(self, name):
        """Callback scheduler saat musik dimulai."""
        log_message(f"MUSIC_EVENT: Jadwal musik '{name}' DIMULAI.")
        self._update_music_flag()
        publish_mqtt(MQTT_TOPIC_AUDIO, "1")

    def _on_music_end(self, name):
        """Callback scheduler saat musik berakhir."""
        log_message(f"MUSIC_EVENT: Jadwal musik '{name}' BERAKHIR.")
        self._update_music_flag()

        if not self.audio_should_be_on:
            log_message(
                f"MUSIC_EVENT: Tidak ada musik/adzan aktif "
                f"setelah '{name}' berakhir. Kirim MQTT '0'."
            )
            publish_mqtt(MQTT_TOPIC_AUDIO, "0")
        else:
            log_message(
                f"MUSIC_EVENT: Masih ada musik/adzan aktif "
                f"setelah '{name}' berakhir. MQTT '0' tidak dikirim."
            )

    # --- Adzan ---

    def schedule_adzan_events(self):
        """Jadwalkan event window aktif/nonaktif adzan berdasarkan waktu sholat."""
        log_message("ADZAN_SCHEDULER: Membersihkan dan membuat jadwal MQTT adzan.")
        schedule.clear('adzan-mqtt-send')
        schedule.clear('adzan-state-control')

        if self.server.get_adzan_active_status() == "0":
            log_message("ADZAN_SCHEDULER: Adzan dinonaktifkan.")
            self.is_adzan_active = False
            return

        prayer_map = self.server.get_prayer_times()
        if not prayer_map:
            log_message("ADZAN_SCHEDULER: Gagal mendapatkan waktu sholat.")
            self.is_adzan_active = False
            return

        now = datetime.now()
        found_active = False

        for name, t_obj in prayer_map.items():
            if t_obj is None:
                continue

            prayer_dt = datetime.combine(date.today(), t_obj)
            window_end = prayer_dt + timedelta(minutes=ADZAN_WINDOW_MINUTES)

            # Cek apakah saat ini di dalam jendela adzan
            if prayer_dt <= now < window_end:
                log_message(f"ADZAN_SCHEDULER: Jendela adzan aktif untuk {name} (startup).")
                self.is_adzan_active = True
                found_active = True

            # Jadwalkan start (jika belum lewat)
            if prayer_dt > now:
                start_hms = t_obj.strftime("%H:%M:%S")
                log_message(
                    f"ADZAN_SCHEDULER: Jadwalkan window active "
                    f"untuk {name} @ {start_hms}"
                )
                schedule.every().day.at(start_hms).do(
                    self._on_adzan_start, name=name
                ).tag('adzan-state-control', f'{name}-start-flag')

                schedule.every().day.at(start_hms).do(
                    publish_mqtt, topic=MQTT_TOPIC_AUDIO, payload="1"
                ).tag('adzan-mqtt-send', f'{name}-start-msg')

            # Jadwalkan end (jika belum lewat)
            if window_end > now:
                end_hms = window_end.strftime("%H:%M:%S")
                log_message(
                    f"ADZAN_SCHEDULER: Jadwalkan window inactive "
                    f"untuk {name} @ {end_hms}"
                )
                schedule.every().day.at(end_hms).do(
                    self._on_adzan_end, name=name
                ).tag('adzan-state-control', f'{name}-end-check')

        if not found_active:
            self.is_adzan_active = False

        log_message(f"ADZAN_SCHEDULER: Selesai. is_adzan_active: {self.is_adzan_active}")

    def _on_adzan_start(self, name):
        """Callback scheduler saat jendela adzan dimulai."""
        self.is_adzan_active = True
        log_message(f"ADZAN_STATE: Jendela adzan untuk {name} AKTIF.")

    def _on_adzan_end(self, name):
        """Callback scheduler saat jendela adzan berakhir."""
        self.is_adzan_active = False
        log_message(f"ADZAN_STATE: Jendela adzan untuk {name} BERAKHIR.")

        if not self.is_music_active:
            log_message(
                f"ADZAN_STATE: Tidak ada musik aktif setelah {name} berakhir. "
                f"Kirim MQTT '0'."
            )
            publish_mqtt(MQTT_TOPIC_AUDIO, "0")
        else:
            log_message(
                f"ADZAN_STATE: Musik aktif setelah {name} berakhir. "
                f"Polling menjaga '1'."
            )

    # --- Utility ---

    @staticmethod
    def _time_in_range(check, start, end):
        """Cek apakah check ada dalam range start-end (support overnight)."""
        if start <= end:
            return start <= check < end
        else:
            return check >= start or check < end


# ===========================================================================
#  LiquidsoapManager — Proses Liquidsoap & file .liq
# ===========================================================================

class LiquidsoapManager:
    """Mengelola proses Liquidsoap, update file .liq, dan file watcher."""

    def __init__(self, target_file, server, scheduler):
        self.target_file = target_file
        self.server = server
        self.scheduler = scheduler
        self.process = None
        self.programmatic_update = threading.Event()
        self.restart_queue = queue.Queue()

    # --- Proses ---

    def start(self):
        """Jalankan proses Liquidsoap."""
        if self.process and self.process.poll() is None:
            log_message("ℹ️ Liquidsoap sudah berjalan.")
            return

        log_message("🚀 Menjalankan Liquidsoap...")
        try:
            if not os.path.exists(self.target_file):
                log_message("❌ Error: File .liq tidak ditemukan.")
                return
            self.process = subprocess.Popen(
                ["liquidsoap", self.target_file],
                stdout=subprocess.DEVNULL,
            )
            log_message(f"✅ Liquidsoap berjalan PID: {self.process.pid}.")
        except Exception as e:
            log_message(f"❌ Error start Liquidsoap: {e}")

    def stop(self):
        """Hentikan proses Liquidsoap."""
        if not self.process or self.process.poll() is not None:
            log_message("ℹ️ Liquidsoap tidak berjalan.")
            return

        log_message("🛑 Menghentikan Liquidsoap...")
        self.process.terminate()
        try:
            self.process.wait(timeout=10)
            log_message("✅ Liquidsoap berhenti.")
        except subprocess.TimeoutExpired:
            log_message("⚠️ Liquidsoap timeout, kill paksa.")
            self.process.kill()
            log_message("✅ Liquidsoap dikill.")
        self.process = None

    def restart(self):
        """Restart: stop → refresh semua data → update .liq → start."""
        log_message("PY_DEBUG: restart_liquidsoap() - Memulai.")
        self.stop()
        self.server.update_location()

        self.programmatic_update.set()
        try:
            self.scheduler.fetch_and_schedule_music()
            self.scheduler.schedule_adzan_events()
            self.update_prayer_time_in_liq()
            time.sleep(0.5)
        finally:
            self.programmatic_update.clear()

        self.start()
        log_message("PY_DEBUG: restart_liquidsoap() - Selesai.")

    # --- Update file .liq ---

    def update_prayer_time_in_liq(self):
        """Update blok jadwal waktu sholat di dalam file .liq."""
        log_message("PY_DEBUG: updatePrayerTime() - Memulai pembaruan di .liq.")

        adzan_status = self.server.get_adzan_active_status()

        if adzan_status == "0":
            log_message("UPDATE_LIQ: Adzan dinonaktifkan. Mengosongkan jadwal.")
            prayer_block = "#=#\n    # Adzan dinonaktifkan dari server\n#=#"
        else:
            log_message("UPDATE_LIQ: Adzan aktif. Membangun jadwal.")
            prayer_block = self._build_prayer_block()

        return self._write_prayer_block(prayer_block, adzan_status)

    def _build_prayer_block(self):
        """Bangun string blok jadwal adzan untuk file .liq."""
        amplify = self.server.get_adzan_amplify()
        amp_str = f"{amplify:.2f}"
        prayer_map = self.server.get_prayer_times()

        if not prayer_map:
            log_message("⚠️ Error: Gagal mendapatkan waktu sholat untuk .liq.")
            return "#=#\n    # Gagal mengambil waktu sholat\n#=#"

        subuh_amp = f'amplify({amp_str}, id="adzan_subuh_amplify", adzan_subuh_playlist)'
        normal_amp = f'amplify({amp_str}, id="adzan_amplify", adzan_playlist)'

        def fmt(key, dh=0, dm=0):
            t = prayer_map.get(key)
            if t:
                return f"{str(t.hour).rjust(2, '0')}h{str(t.minute).rjust(2, '0')}m"
            return f"{str(dh).rjust(2, '0')}h{str(dm).rjust(2, '0')}m"

        return f"""#=#
            ({{ {fmt("Fajr")} }}, {subuh_amp}), #subuh
            ({{ {fmt("Dhuhr")} }}, {normal_amp}), #dhuhur
            ({{ {fmt("Asr")} }}, {normal_amp}), #ashar
            ({{ {fmt("Maghrib")} }}, {normal_amp}), #maghrib
            ({{ {fmt("Isha")} }}, {normal_amp}), #isya
        #=#"""

    def _write_prayer_block(self, prayer_block, adzan_status):
        """Tulis blok jadwal ke file .liq, ganti antara penanda #=#..#=#."""
        try:
            with open(self.target_file, "r", encoding="utf-8") as f:
                content = f.read()

            matches = re.findall(r'#=#.*?#=#', content, re.DOTALL)
            if not matches:
                log_message("⚠️ Error: Penanda #=#...#=# tidak ditemukan.")
                return False

            content = content.replace(matches[0], prayer_block)

            with open(self.target_file, 'w', encoding="utf-8") as f:
                f.write(content)

            if adzan_status != "0":
                log_message(
                    f"✅ Jadwal di {self.target_file} diperbarui. "
                    f"Amplify: {self.server.get_adzan_amplify():.2f}"
                )
            else:
                log_message(
                    f"✅ Jadwal di {self.target_file} diperbarui. "
                    f"Adzan dinonaktifkan."
                )
            return True
        except Exception as e:
            log_message(f"❌ Error update .liq: {e}")
            return False

    # --- Restart Queue ---

    def request_restart(self, reason="RESTART_REQUESTED"):
        """Masukkan request restart ke queue (jika belum ada)."""
        if self.restart_queue.empty():
            log_message(f"PY_DEBUG: {reason}")
            self.restart_queue.put(reason)

    def process_restart_queue(self):
        """Proses satu request restart dari queue jika ada."""
        try:
            request = self.restart_queue.get_nowait()
            if request in ("RESTART_REQUESTED", "RESTART_REQUESTED_BY_DAILY_SCHEDULER"):
                log_message(f"PY_DEBUG: Main loop - Menerima '{request}'.")
                self.restart()
        except queue.Empty:
            pass


# ===========================================================================
#  FileChangeHandler
# ===========================================================================

class FileChangeHandler(FileSystemEventHandler):
    """Mendeteksi perubahan eksternal pada file .liq dan request restart."""

    def __init__(self, manager):
        super().__init__()
        self.manager = manager

    def on_modified(self, event):
        if os.path.abspath(event.src_path) != self.manager.target_file:
            return
        if self.manager.programmatic_update.is_set():
            log_message("PY_DEBUG: Perubahan terprogram, abaikan.")
            return
        self.manager.request_restart("Perubahan EKSTERNAL, request restart.")


# ===========================================================================
#  Main
# ===========================================================================

def parse_arguments():
    """Parse command line arguments."""
    parser = argparse.ArgumentParser(description='Liquidsoap Radio Scheduler')
    parser.add_argument(
        '--liq-file',
        type=str,
        default=DEFAULT_LIQ_FILE_PATH,
        help=f'Path ke file .liq (default: {DEFAULT_LIQ_FILE_PATH})',
    )
    return parser.parse_args()


def main():
    args = parse_arguments()
    target_file = os.path.abspath(args.liq_file)
    watch_dir = os.path.dirname(target_file)

    if not os.path.exists(watch_dir):
        log_message(f"❌ Error: Direktori tidak ada: {watch_dir}")
        exit(1)

    log_message("🚀 Skrip dimulai...")
    log_message(f"📁 Menggunakan file .liq: {target_file}")

    # --- Inisialisasi ---
    server = ServerAPI()
    scheduler = AudioScheduler(server)
    manager = LiquidsoapManager(target_file, server, scheduler)

    # Default MQTT OFF
    publish_mqtt(topic=MQTT_TOPIC_AUDIO, payload="0")

    # Update lokasi dari server
    server.update_location()

    # --- Setup awal ---
    manager.programmatic_update.set()
    try:
        scheduler.fetch_and_schedule_music()
        scheduler.schedule_adzan_events()
        manager.update_prayer_time_in_liq()
    finally:
        manager.programmatic_update.clear()
    log_message("INIT: Pembaruan awal selesai.")

    # --- Jadwal harian (refresh jam 00:00) ---
    schedule.every().day.at("00:00").do(
        manager.request_restart,
        reason="RESTART_REQUESTED_BY_DAILY_SCHEDULER",
    ).tag('daily-refresh')
    log_message("SCHEDULER: Refresh harian dijadwalkan pukul 00:00.")

    # --- File watcher ---
    handler = FileChangeHandler(manager)
    observer = Observer()
    observer.schedule(handler, path=watch_dir, recursive=False)
    observer.start()
    log_message(f"👀 Memantau direktori: {watch_dir}")

    # --- Start Liquidsoap ---
    manager.start()
    log_message("   Tekan Ctrl+C untuk keluar.")

    # --- Main loop ---
    next_mqtt_time = time.time()

    try:
        while True:
            schedule.run_pending()

            # Polling MQTT periodik
            now = time.time()
            if now >= next_mqtt_time:
                payload = scheduler.audio_payload
                log_message(
                    f"PERIODIC_MQTT: Music: {scheduler.is_music_active}, "
                    f"Adzan: {scheduler.is_adzan_active}. Sending: '{payload}'"
                )
                publish_mqtt(topic=MQTT_TOPIC_AUDIO, payload=payload)
                next_mqtt_time = now + MQTT_POLL_INTERVAL

            # Proses restart request
            manager.process_restart_queue()

            time.sleep(0.1)

    except KeyboardInterrupt:
        log_message("\n🛑 Ctrl+C diterima. Keluar...")
    except Exception as e:
        log_message(f"❌ Error loop utama: {e}")
    finally:
        log_message("PY_DEBUG: Main loop - Shutdown...")
        manager.stop()
        if observer.is_alive():
            observer.stop()
            log_message("👁️ Observer berhenti.")
        observer.join()
        log_message("👋 Skrip selesai.")


if __name__ == "__main__":
    main()
