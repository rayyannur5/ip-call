import os
import time
import subprocess
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
from datetime import date, datetime, time as dt_time, timedelta 
from pyIslam.praytimes import ( 
    PrayerConf,
    Prayer,
    # LIST_FAJR_ISHA_METHODS, # This import was unused
)
import re
import threading 
import queue 
import schedule 
import requests # Untuk HTTP requests
import json # Untuk parsing JSON
import paho.mqtt.client as mqtt # Untuk MQTT

# --- Konfigurasi Logging ---
def log_message(message):
    """Mencetak pesan dengan timestamp."""
    if message.startswith("PY_DEBUG:"): 
         return 
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S.%f")[:-3] 
    print(f"[{timestamp}] {message}")

# --- Konfigurasi Aplikasi ---
DEFAULT_LATITUDE = -7.288354699999995
DEFAULT_LONGITUDE = 112.72549628465647
latitude = DEFAULT_LATITUDE
longitude = DEFAULT_LONGITUDE

liq_file_path = "/opt/lampp/htdocs/ip-call/liquidsoap/radio.liq" 
target_file = os.path.abspath(liq_file_path)
music_schedule_url = "http://localhost/ip-call/server/music.php" 
utils_url = "http://localhost/ip-call/server/utils.php" 
adzan_schedule_url = "http://localhost/ip-call/server/adzan.php"

# --- Konfigurasi MQTT ---
mqtt_broker_address = "localhost" 
mqtt_broker_port = 1883
mqtt_topic_schedule_audio = "schedule_audio"
mqtt_client_id = f"liquidsoap_scheduler_{os.getpid()}"

# --- Variabel Global ---
liquidsoap_process = None
programmatic_update_event = threading.Event() 
restart_request_queue = queue.Queue() 
current_music_schedules_data = [] # Cache untuk jadwal musik
is_music_supposed_to_be_active = False # Flag status musik berdasarkan jadwal musik
is_adzan_window_active = False # Flag status jendela waktu adzan (adzan s/d +5 menit)

# --- Fungsi MQTT ---
def publish_mqtt_message(topic, payload):
    """Mempublikasikan pesan MQTT dengan QoS 1 dan Retain True."""
    try:
        unique_client_id = mqtt_client_id + f"_pub_{time.time_ns()}"
        client = mqtt.Client()
        client.connect(mqtt_broker_address, mqtt_broker_port, 60)
        client.loop_start() 
        result = client.publish(topic, payload, qos=1, retain=True)
        status = result.rc
        if status == mqtt.MQTT_ERR_SUCCESS:
            log_message(f"MQTT: Pesan '{payload}' (QoS 1, Retain True) berhasil dikirim ke topik '{topic}'. MID: {result.mid if result else 'N/A'}")
        else:
            log_message(f"MQTT: Gagal mengirim pesan (QoS 1, Retain True) ke topik '{topic}', kode status: {status}")
        client.loop_stop() 
        client.disconnect()
    except Exception as e:
        log_message(f"MQTT: Error saat publikasi - {e}")

# --- Fungsi Pengambilan Data Server ---
def get_server_utils_data():
    """Mengambil semua data dari server utils.php."""
    try:
        response = requests.get(utils_url, timeout=5)
        response.raise_for_status()
        utils_data = response.json()
        if utils_data.get("success") and "data" in utils_data:
            return utils_data["data"]
        log_message("UTILS_DATA: Respons data utils tidak sukses atau format tidak sesuai.")
    except requests.exceptions.RequestException as e:
        log_message(f"UTILS_DATA: Error mengambil data utils: {e}.")
    except json.JSONDecodeError as e:
        log_message(f"UTILS_DATA: Error parsing JSON dari data utils: {e}.")
    except Exception as e:
        log_message(f"UTILS_DATA: Error tidak terduga saat mengambil data utils: {e}.")
    return None

def update_location_coordinates():
    """Memperbarui variabel global latitude dan longitude dari server."""
    global latitude, longitude
    log_message("LOCATION: Mencoba memperbarui koordinat lokasi dari server...")
    utils_list = get_server_utils_data()
    new_lat, new_lon = None, None
    if utils_list:
        for item in utils_list:
            item_type, item_value = item.get("type"), item.get("value")
            if item_type == "adzan_latitude":
                try: new_lat = float(item_value); log_message(f"LOCATION: Latitude dari server: {new_lat}")
                except (ValueError, TypeError): log_message(f"LOCATION: Nilai latitude tidak valid: {item_value}")
            elif item_type == "adzan_longitude":
                try: new_lon = float(item_value); log_message(f"LOCATION: Longitude dari server: {new_lon}")
                except (ValueError, TypeError): log_message(f"LOCATION: Nilai longitude tidak valid: {item_value}")
        latitude = new_lat if new_lat is not None else latitude
        longitude = new_lon if new_lon is not None else longitude
    else:
        log_message("LOCATION: Gagal mengambil data utils. Koordinat tidak diperbarui.")
    log_message(f"LOCATION: Koordinat saat ini: Latitude={latitude}, Longitude={longitude}")

def get_adzan_amplify_factor():
    default_amplify = 1.0 
    utils_list = get_server_utils_data()
    if utils_list:
        for item in utils_list:
            if item.get("type") == "adzan_volume":
                try: return float(item.get("value")) / 100.0
                except (ValueError, TypeError): log_message(f"AMPLIFY: Nilai volume adzan tidak valid. Menggunakan default."); return default_amplify
        log_message("AMPLIFY: Tipe 'adzan_volume' tidak ditemukan. Menggunakan default.")
    else: log_message("AMPLIFY: Gagal mengambil data utils. Menggunakan default.")
    return default_amplify

def get_adzan_active_status():
    utils_list = get_server_utils_data()
    if utils_list:
        for item in utils_list:
            if item.get("type") == "adzan_active": return item.get("value", "1")
        log_message("ADZAN_STATUS: Tipe 'adzan_active' tidak ditemukan. Mengasumsikan aktif ('1').")
    else: log_message("ADZAN_STATUS: Gagal mengambil data utils. Mengasumsikan aktif ('1').")
    return "1"

def get_prayer_times_map():
    global latitude, longitude
    log_message("PRAYER_TIMES: Memulai pengambilan data waktu sholat.")
    adzan_auto_status = "1" 
    utils_list = get_server_utils_data()
    if utils_list:
        for item in utils_list:
            if item.get("type") == "adzan_auto": adzan_auto_status = item.get("value", "1"); break
    if adzan_auto_status == "0":
        log_message("PRAYER_TIMES: adzan_auto=0, mencoba mengambil dari adzan.php.")
        try:
            response = requests.get(adzan_schedule_url, timeout=5); response.raise_for_status()
            manual_schedules, prayer_map, key_mapping, valid_times = response.json(), {}, {"subuh": "Fajr", "dhuhur": "Dhuhr", "ashar": "Asr", "maghrib": "Maghrib", "isya": "Isha"}, 0
            for item in manual_schedules:
                prayer_name = key_mapping.get(item.get("key"))
                if prayer_name and item.get("value"):
                    try: prayer_map[prayer_name] = dt_time.fromisoformat(item.get("value")); valid_times +=1
                    except ValueError: log_message(f"PRAYER_TIMES: Format waktu manual tidak valid: {item}")
            if valid_times == 5: log_message("PRAYER_TIMES: Berhasil mengambil waktu manual."); return prayer_map
            log_message(f"PRAYER_TIMES: Gagal mengambil semua waktu manual ({valid_times}/5). Fallback.")
        except Exception as e: log_message(f"PRAYER_TIMES: Error jadwal manual: {e}. Fallback.")
    log_message(f"PRAYER_TIMES: Menggunakan pyIslam Lat: {latitude}, Lon: {longitude}.")
    p_conf = PrayerConf(longitude, latitude, 7, 7, 1); today_prayers = Prayer(p_conf, date.today())
    return {"Fajr": today_prayers.fajr_time(), "Dhuhr": today_prayers.dohr_time(), "Asr": today_prayers.asr_time(), "Maghrib": today_prayers.maghreb_time(), "Isha": today_prayers.ishaa_time()}

# --- Fungsi Update Status dan Penjadwalan ---
def update_music_state_and_flag():
    """
    Memeriksa jadwal musik yang dimuat (current_music_schedules_data) terhadap waktu saat ini.
    Memperbarui flag global is_music_supposed_to_be_active.
    """
    global is_music_supposed_to_be_active, current_music_schedules_data
    
    now_time = datetime.now().time()
    active_found_in_current_schedules = False

    if not current_music_schedules_data:
        log_message("PY_DEBUG: update_music_state_and_flag - Tidak ada jadwal musik yang dimuat.")
        if is_music_supposed_to_be_active: 
             log_message("MUSIC_STATE_UPDATE: Musik menjadi TIDAK AKTIF (tidak ada jadwal).")
        is_music_supposed_to_be_active = False
        return

    for item in current_music_schedules_data:
        try:
            start_time_str, end_time_str = item.get("start_time"), item.get("end_time")
            if not start_time_str or not end_time_str: continue
            start_t, end_t = dt_time.fromisoformat(start_time_str), dt_time.fromisoformat(end_time_str)
            if (start_t <= end_t and start_t <= now_time < end_t) or \
               (start_t > end_t and (now_time >= start_t or now_time < end_t)):
                active_found_in_current_schedules = True; break
        except ValueError: log_message(f"PY_DEBUG: update_music_state_and_flag - Format waktu tidak valid: {item}"); continue
        except Exception as e: log_message(f"PY_DEBUG: update_music_state_and_flag - Error proses item: {e}"); continue
            
    if active_found_in_current_schedules and not is_music_supposed_to_be_active:
        log_message("MUSIC_STATE_UPDATE: Musik menjadi AKTIF berdasarkan jadwal.")
    elif not active_found_in_current_schedules and is_music_supposed_to_be_active:
        log_message("MUSIC_STATE_UPDATE: Musik menjadi TIDAK AKTIF berdasarkan jadwal.")
    is_music_supposed_to_be_active = active_found_in_current_schedules
    log_message(f"PY_DEBUG: update_music_state_and_flag - is_music_supposed_to_be_active: {is_music_supposed_to_be_active}")

def handle_music_schedule_start(playlist_name):
    """Dipanggil oleh scheduler saat jadwal musik dimulai."""
    log_message(f"MUSIC_EVENT: Jadwal musik '{playlist_name}' DIMULAI.")
    update_music_state_and_flag() # Update flag status global
    # Pengiriman MQTT '1' akan ditangani oleh polling periodik,
    # atau jika ingin lebih direct, bisa ditambahkan di sini:
    publish_mqtt_message(mqtt_topic_schedule_audio, "1")
    # Namun, untuk konsistensi dengan polling, lebih baik biarkan polling yang mengirim.
    # Jika ada adzan window aktif, polling akan tetap mengirim '1'.

def handle_music_schedule_end(playlist_name):
    """Dipanggil oleh scheduler saat jadwal musik berakhir."""
    global is_music_supposed_to_be_active, is_adzan_window_active
    log_message(f"MUSIC_EVENT: Jadwal musik '{playlist_name}' BERAKHIR.")
    update_music_state_and_flag() # Update flag status global
    
    # Setelah flag diupdate, cek apakah kita perlu mengirim '0'
    # Ini hanya dilakukan jika TIDAK ada musik lain yang aktif DAN TIDAK ada jendela adzan
    if not is_music_supposed_to_be_active and not is_adzan_window_active:
        log_message(f"MUSIC_EVENT: Tidak ada musik lain atau adzan aktif setelah '{playlist_name}' berakhir. Mengirim MQTT '0'.")
        publish_mqtt_message(mqtt_topic_schedule_audio, "0")
    else:
        log_message(f"MUSIC_EVENT: Ada musik lain atau adzan aktif setelah '{playlist_name}' berakhir. MQTT '0' TIDAK dikirim oleh event ini.")


def fetch_and_schedule_music():
    """
    Mengambil jadwal musik, menyimpannya global, menjadwalkan event start/end, 
    & update status saat ini (yang akan dipickup polling).
    """
    global current_music_schedules_data, is_music_supposed_to_be_active
    log_message("MUSIC_SCHEDULER: Memproses jadwal musik...")
    schedules_data_fetched = None
    any_playlist_active_for_immediate_evaluation = False
    try:
        response = requests.get(music_schedule_url, timeout=10); response.raise_for_status() 
        schedules_data_fetched = response.json()
        log_message(f"MUSIC_SCHEDULER: Menerima {len(schedules_data_fetched or [])} jadwal musik.")
    except Exception as e: log_message(f"MUSIC_SCHEDULER: Error mengambil/parsing jadwal musik: {e}")
    
    current_music_schedules_data = schedules_data_fetched or [] # Simpan atau kosongkan
    
    schedule.clear('music-schedule-event'); log_message("MUSIC_SCHEDULER: Jadwal event musik lama dibersihkan.")
    
    now_time = datetime.now().time()

    if current_music_schedules_data:
        for item in current_music_schedules_data:
            try:
                name, start_str, end_str = item.get("name", "N/A"), item.get("start_time"), item.get("end_time")
                if not start_str or not end_str: log_message(f"MUSIC_SCHEDULER: Lewati '{name}', start/end time hilang."); continue
                
                start_t_obj = dt_time.fromisoformat(start_str)
                end_t_obj = dt_time.fromisoformat(end_str)
                start_sched, end_sched = start_t_obj.strftime("%H:%M"), end_t_obj.strftime("%H:%M")

                log_message(f"MUSIC_SCHEDULER: Jadwalkan event untuk '{name}' @ Start: {start_sched}, End: {end_sched}")
                schedule.every().day.at(start_sched).do(handle_music_schedule_start, playlist_name=name).tag('music-schedule-event', f'event-start-{name}')
                schedule.every().day.at(end_sched).do(handle_music_schedule_end, playlist_name=name).tag('music-schedule-event', f'event-end-{name}')

                # Cek apakah slot ini aktif saat ini untuk evaluasi awal
                if (start_t_obj <= end_t_obj and start_t_obj <= now_time < end_t_obj) or \
                   (start_t_obj > end_t_obj and (now_time >= start_t_obj or now_time < end_t_obj)):
                    any_playlist_active_for_immediate_evaluation = True
            
            except ValueError: log_message(f"MUSIC_SCHEDULER: Format waktu tidak valid untuk '{name}'. Dilewati."); continue
            except Exception as e: log_message(f"MUSIC_SCHEDULER: Error jadwal item musik '{name}': {e}")

    # Update flag global berdasarkan jadwal yang baru dimuat dan waktu saat ini
    update_music_state_and_flag() 
    
    # Jika setelah memuat jadwal, tidak ada musik yang seharusnya aktif SEKARANG,
    # dan tidak ada jendela adzan aktif, maka pastikan MQTT '0' dikirim (jika belum).
    # Ini ditangani oleh polling periodik, tetapi bisa juga dipercepat di sini.
    # Namun, untuk konsistensi, kita biarkan polling yang menangani pengiriman MQTT berdasarkan flag.
    # Initial '0' sudah dikirim saat startup.
    if not is_music_supposed_to_be_active and not is_adzan_window_active:
        log_message("MUSIC_SCHEDULER: Setelah memuat jadwal, tidak ada musik atau adzan aktif. Status MQTT akan jadi '0' oleh polling.")
    elif is_music_supposed_to_be_active:
         log_message("MUSIC_SCHEDULER: Setelah memuat jadwal, musik terdeteksi aktif. Status MQTT akan jadi '1' oleh polling.")


    log_message("MUSIC_SCHEDULER: Pemrosesan & penjadwalan event musik selesai.")


def is_any_music_active_now(): 
    global is_music_supposed_to_be_active
    # Fungsi ini sekarang hanya mengembalikan flag yang diupdate oleh update_music_state_and_flag()
    # dan event scheduler musik.
    return is_music_supposed_to_be_active

# --- Fungsi Terjadwal untuk Adzan ---
def set_adzan_window_active_true(prayer_name):
    global is_adzan_window_active
    is_adzan_window_active = True
    log_message(f"ADZAN_STATE_CTRL: Jendela adzan untuk {prayer_name} AKTIF.")
    # Kita tidak mengirim MQTT '1' di sini lagi, biarkan polling yang melakukannya
    # berdasarkan kombinasi is_adzan_window_active dan is_music_supposed_to_be_active

def set_adzan_window_active_false_and_check_music(prayer_name):
    global is_adzan_window_active, is_music_supposed_to_be_active
    is_adzan_window_active = False
    log_message(f"ADZAN_STATE_CTRL: Jendela adzan untuk {prayer_name} BERAKHIR.")
    # Setelah jendela adzan berakhir, jika tidak ada musik yang seharusnya aktif,
    # polling akan mengirim '0'. Jika musik seharusnya aktif, polling akan mengirim '1'.
    # Tidak perlu mengirim '0' secara eksplisit di sini kecuali ada logika khusus.
    # Untuk saat ini, kita biarkan polling yang menangani transisi akhir.
    # Jika ingin memastikan '0' jika tidak ada musik:
    if not is_music_supposed_to_be_active:
        log_message(f"ADZAN_STATE_CTRL: Tidak ada musik aktif setelah {prayer_name} berakhir. Polling akan mengirim '0'.")
        publish_mqtt_message(mqtt_topic_schedule_audio, "0") # Opsional: kirim '0' segera jika tidak ada musik
    else:
        log_message(f"ADZAN_STATE_CTRL: Musik aktif setelah {prayer_name} berakhir. Polling akan menjaga '1'.")


def schedule_adzan_mqtt_events():
    global is_adzan_window_active 
    log_message("ADZAN_MQTT_SCHEDULER: Membersihkan dan membuat jadwal MQTT adzan.")
    schedule.clear('adzan-mqtt-send'); schedule.clear('adzan-state-control') 
    adzan_active_status = get_adzan_active_status()
    if adzan_active_status == "0":
        log_message("ADZAN_MQTT_SCHEDULER: Adzan dinonaktifkan."); is_adzan_window_active = False; return
    prayer_times_map = get_prayer_times_map(); 
    if not prayer_times_map:
        log_message("ADZAN_MQTT_SCHEDULER: Gagal mendapatkan waktu sholat."); is_adzan_window_active = False; return
    now_datetime = datetime.now(); found_active_adzan_window_on_startup = False
    for prayer_name, prayer_time_obj in prayer_times_map.items():
        if prayer_time_obj is None: continue
        prayer_event_datetime = datetime.combine(date.today(), prayer_time_obj)
        adzan_window_end_datetime = prayer_event_datetime + timedelta(minutes=5)
        if prayer_event_datetime <= now_datetime < adzan_window_end_datetime:
            log_message(f"ADZAN_MQTT_SCHEDULER: Startup - Jendela adzan aktif untuk {prayer_name}.")
            is_adzan_window_active = True; found_active_adzan_window_on_startup = True
        if prayer_event_datetime > now_datetime:
            sched_time_job = prayer_time_obj.strftime("%H:%M:%S") 
            log_message(f"ADZAN_MQTT_SCHEDULER: Jadwalkan MQTT '1' (via polling) & set window active untuk {prayer_name} @ {sched_time_job}")
            # Hanya set flag, biarkan polling mengirim MQTT
            schedule.every().day.at(sched_time_job).do(set_adzan_window_active_true, prayer_name=prayer_name).tag('adzan-state-control', f'{prayer_name}-start-flag')
            # Jika ingin mengirim MQTT '1' langsung saat adzan dimulai, tambahkan job ini:
            schedule.every().day.at(sched_time_job).do(publish_mqtt_message,topic=mqtt_topic_schedule_audio,payload="1").tag('adzan-mqtt-send', f'{prayer_name}-start-msg')

        if adzan_window_end_datetime > now_datetime: 
            sched_time_check = adzan_window_end_datetime.strftime("%H:%M:%S")
            log_message(f"ADZAN_MQTT_SCHEDULER: Jadwalkan set window inactive & check untuk {prayer_name} @ {sched_time_check}")
            schedule.every().day.at(sched_time_check).do(set_adzan_window_active_false_and_check_music,prayer_name=prayer_name).tag('adzan-state-control', f'{prayer_name}-end-check')
    if not found_active_adzan_window_on_startup: is_adzan_window_active = False
    log_message(f"ADZAN_MQTT_SCHEDULER: Selesai. is_adzan_window_active: {is_adzan_window_active}")

# --- Fungsi-Fungsi Aplikasi (Lanjutan) ---
def updatePrayerTime():
    global target_file
    log_message("PY_DEBUG: updatePrayerTime() - Memulai pembaruan waktu sholat di .liq.") 
    adzan_active_status = get_adzan_active_status(); prayertext = ""
    if adzan_active_status == "0":
        log_message("UPDATE_PRAYER_TIME_LIQ: Adzan dinonaktifkan. Mengosongkan jadwal di .liq.")
        prayertext = "#=#\n    # Adzan dinonaktifkan dari server\n#=#"
    else:
        log_message("UPDATE_PRAYER_TIME_LIQ: Adzan aktif. Membangun jadwal untuk .liq.")
        amplify_factor = get_adzan_amplify_factor(); amplify_str = f"{amplify_factor:.2f}" 
        prayer_times_map = get_prayer_times_map() 
        if not prayer_times_map:
            log_message("⚠️ Error: Gagal mendapatkan waktu sholat untuk .liq."); prayertext = "#=#\n    # Gagal mengambil waktu sholat\n#=#" 
        else:
            ads_pl_amp = f"amplify({amplify_str}, id=\"adzan_subuh_amplify\", adzan_subuh_playlist)"; ad_pl_amp = f"amplify({amplify_str}, id=\"adzan_amplify\", adzan_playlist)"
            def get_ts(pk, dh=0, dm=0): t_obj = prayer_times_map.get(pk); return f"{str(t_obj.hour).rjust(2,'0')}h{str(t_obj.minute).rjust(2,'0')}m" if t_obj else f"{str(dh).rjust(2,'0')}h{str(dm).rjust(2,'0')}m"
            prayertext = f"""#=#
            ({{ {get_ts("Fajr")} }}, {ads_pl_amp}), #subuh
            ({{ {get_ts("Dhuhr")} }}, {ad_pl_amp}), #dhuhur
            ({{ {get_ts("Asr")} }}, {ad_pl_amp}), #ashar
            ({{ {get_ts("Maghrib")} }}, {ad_pl_amp}), #maghrib
            ({{ {get_ts("Isha")} }}, {ad_pl_amp}), #isya
        #=#"""
    try:
        with open(target_file, "r", encoding="utf-8") as f: str_liq_file = f.read()
        matches = re.findall(r'#=#.*?#=#', str_liq_file, re.DOTALL)
        if not matches: log_message(f"⚠️ Error: Penanda #=#...#=# tidak ditemukan."); return False 
        str_liq_file = str_liq_file.replace(matches[0], prayertext)
        with open(target_file, 'w', encoding="utf-8") as f: f.write(str_liq_file)
        log_message(f"✅ Jadwal di {target_file} diperbarui." + (f" Amplify: {get_adzan_amplify_factor():.2f}" if adzan_active_status != "0" else " Adzan dinonaktifkan."))
        return True
    except Exception as e: log_message(f"❌ Error update .liq: {e}"); return False

def start_liquidsoap():
    global liquidsoap_process, target_file
    if liquidsoap_process and liquidsoap_process.poll() is None: log_message("ℹ️ Liquidsoap sudah berjalan."); return
    log_message("🚀 Menjalankan Liquidsoap...");
    try:
        if not os.path.exists(target_file): log_message(f"❌ Error: File .liq tidak ditemukan."); return
        liquidsoap_process = subprocess.Popen(["liquidsoap", target_file], stdout=subprocess.DEVNULL)
        log_message(f"✅ Liquidsoap berjalan PID: {liquidsoap_process.pid}.")
    except Exception as e: log_message(f"❌ Error start Liquidsoap: {e}")

def stop_liquidsoap():
    global liquidsoap_process
    if liquidsoap_process and liquidsoap_process.poll() is None:
        log_message("🛑 Menghentikan Liquidsoap..."); liquidsoap_process.terminate()
        try: liquidsoap_process.wait(timeout=10); log_message("✅ Liquidsoap berhenti.")
        except subprocess.TimeoutExpired: log_message("⚠️ Liquidsoap timeout, kill paksa."); liquidsoap_process.kill(); log_message("✅ Liquidsoap dikill.")
        liquidsoap_process = None
    else: log_message("ℹ️ Liquidsoap tidak berjalan.")

def restart_liquidsoap():
    global programmatic_update_event
    log_message(f"PY_DEBUG: restart_liquidsoap() - Memulai.") 
    stop_liquidsoap(); update_location_coordinates() 
    programmatic_update_event.set() 
    try:
        fetch_and_schedule_music() 
        schedule_adzan_mqtt_events() 
        updatePrayerTime() 
        time.sleep(0.5) 
    finally:
        programmatic_update_event.clear() 
    start_liquidsoap()
    log_message(f"PY_DEBUG: restart_liquidsoap() - Selesai.") 

class FileChangeHandler(FileSystemEventHandler):
    def on_modified(self, event):
        global programmatic_update_event, target_file, restart_request_queue
        if os.path.abspath(event.src_path) == target_file:
            if programmatic_update_event.is_set(): log_message(f"PY_DEBUG: Perubahan terprogram, abaikan."); return 
            if restart_request_queue.empty(): log_message(f"PY_DEBUG: Perubahan EKSTERNAL, request restart."); restart_request_queue.put("RESTART_REQUESTED")

def scheduled_daily_refresh_trigger(): 
    log_message("SCHEDULER_DAILY_REFRESH: Sinyal refresh harian.");
    if restart_request_queue.empty(): restart_request_queue.put("RESTART_REQUESTED_BY_DAILY_SCHEDULER")

if __name__ == "__main__":
    watch_dir = os.path.dirname(target_file)
    if not os.path.exists(watch_dir): log_message(f"❌ Error: Direktori tidak ada: {watch_dir}"); exit(1)
    
    log_message("🚀 Skrip dimulai..."); 
    publish_mqtt_message(topic=mqtt_topic_schedule_audio, payload="0") # Default OFF
    
    update_location_coordinates() 
    
    programmatic_update_event.set() 
    try: 
        fetch_and_schedule_music()    
        schedule_adzan_mqtt_events()  
        updatePrayerTime() 
    finally: 
        programmatic_update_event.clear() 
    log_message(f"INIT: Pembaruan awal selesai.") 
    
    schedule.every().day.at("00:00").do(scheduled_daily_refresh_trigger).tag('daily-refresh') 
    log_message("SCHEDULER_DAILY_REFRESH: Refresh harian dijadwalkan pukul 00:00.")
    
    event_handler = FileChangeHandler(); observer = Observer()
    observer.schedule(event_handler, path=watch_dir, recursive=False); observer.start()
    log_message(f"👀 Memantau direktori: {watch_dir}")
    
    start_liquidsoap(); 
    log_message("   Tekan Ctrl+C untuk keluar.")
    
    next_periodic_mqtt_send_time = time.time() 

    try:
        while True:
            schedule.run_pending() 
            
            current_time = time.time()
            if current_time >= next_periodic_mqtt_send_time:
                audio_should_be_on = is_music_supposed_to_be_active or is_adzan_window_active
                current_payload = "1" if audio_should_be_on else "0"
                log_message(f"PERIODIC_MQTT: Music Active: {is_music_supposed_to_be_active}, Adzan Window: {is_adzan_window_active}. Sending: '{current_payload}'")
                publish_mqtt_message(topic=mqtt_topic_schedule_audio, payload=current_payload)
                next_periodic_mqtt_send_time = current_time + 30
            
            try:
                request = restart_request_queue.get_nowait() 
                if request in ["RESTART_REQUESTED", "RESTART_REQUESTED_BY_DAILY_SCHEDULER"]: 
                    log_message(f"PY_DEBUG: Main loop - Menerima '{request}'.") 
                    restart_liquidsoap() 
            except queue.Empty: pass 
            
            time.sleep(0.1) 
    except KeyboardInterrupt: log_message("\n🛑 Ctrl+C diterima. Keluar...")
    except Exception as e: log_message(f"❌ Error loop utama: {e}")
    finally:
        log_message("PY_DEBUG: Main loop - Shutdown..."); stop_liquidsoap()
        if observer.is_alive(): observer.stop(); log_message("👁️ Observer berhenti.")
        observer.join(); log_message("👋 Skrip selesai.")

