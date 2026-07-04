import requests
import time
from datetime import datetime

# ==============================================================================
# Konfigurasi
# ==============================================================================
# URL untuk mengambil dan mengatur data jadwal.
# Menggunakan format f-string untuk memudahkan penambahan parameter.
GET_SCHEDULE_URL = "http://localhost/ip-call/server/hour/get.php"
SET_VOLUME_URL = "http://localhost/ip-call/server/hour/set.php?vol={vol}"

# Interval polling dalam detik.
POLL_INTERVAL_SECONDS = 30

# ==============================================================================
# Fungsi Logging
# ==============================================================================
def log_print(message, level="INFO"):
    """
    Mencetak pesan log dengan format stempel waktu dan level.
    Contoh: [2025-06-23 10:15:00] [INFO] Pesan log.
    """
    timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] [{level.upper()}] {message}")

# ==============================================================================
# Fungsi Inti
# ==============================================================================

def fetch_schedule_data():
    """
    Mengambil data jadwal dari server.
    Mengembalikan data jadwal dalam format list of dicts, atau None jika gagal.
    """
    try:
        log_print(f"Mengambil jadwal dari {GET_SCHEDULE_URL}")
        response = requests.get(GET_SCHEDULE_URL, timeout=10) # Tambahkan timeout
        response.raise_for_status()  # Akan memunculkan error untuk status 4xx/5xx
        
        data = response.json()
        if 'data' not in data or not isinstance(data['data'], list):
            log_print("Format JSON tidak valid atau key 'data' tidak ditemukan.", level="ERROR")
            return None
            
        return data['data']
        
    except requests.exceptions.RequestException as e:
        log_print(f"Gagal mengambil data dari server: {e}", level="ERROR")
        return None
    except ValueError: # Termasuk json.JSONDecodeError
        log_print("Gagal mem-parsing respons JSON dari server.", level="ERROR")
        return None

def find_applicable_entry(schedule_data):
    """
    Mencari entri jadwal yang relevan berdasarkan waktu saat ini.
    
    Logika:
    1. Ambil waktu saat ini.
    2. Filter jadwal untuk menemukan semua waktu yang sudah lewat hari ini.
    3. Jika ada waktu yang sudah lewat, ambil yang paling baru (terbesar).
    4. Jika TIDAK ada waktu yang lewat (misal, sebelum jadwal pertama hari ini),
       maka ambil jadwal terakhir dari hari sebelumnya (entri dengan waktu terbesar
       dari keseluruhan jadwal).
    
    Mengembalikan objek (dict) entri yang sesuai, atau None jika tidak ditemukan.
    """
    if not schedule_data:
        log_print("Data jadwal kosong atau tidak valid.", level="WARNING")
        return None

    try:
        format_waktu = "%H:%M:%S"
        waktu_sekarang = datetime.now().time()
        log_print(f"Waktu referensi saat ini: {waktu_sekarang.strftime(format_waktu)}")

        # Filter untuk menemukan semua waktu dalam jadwal yang lebih kecil dari waktu sekarang
        waktu_yang_sudah_lewat = [
            item for item in schedule_data 
            if datetime.strptime(item['time'], format_waktu).time() < waktu_sekarang
        ]
        
        entri_terpilih = None
        if waktu_yang_sudah_lewat:
            # Jika ada jadwal yang terlewat hari ini, ambil yang paling akhir (terbaru)
            log_print(f"Ditemukan {len(waktu_yang_sudah_lewat)} jadwal yang sudah lewat hari ini.")
            entri_terpilih = max(
                waktu_yang_sudah_lewat, 
                key=lambda x: datetime.strptime(x['time'], format_waktu).time()
            )
        else:
            # Jika tidak ada jadwal yang terlewat hari ini (misal, masih pagi),
            # ambil entri terakhir dari seluruh jadwal (dianggap dari hari kemarin)
            log_print("Tidak ada jadwal yang terlewat hari ini. Mengambil jadwal terakhir dari daftar.")
            entri_terpilih = max(
                schedule_data, 
                key=lambda x: datetime.strptime(x['time'], format_waktu).time()
            )
            
        return entri_terpilih

    except (KeyError, TypeError) as e:
        log_print(f"Struktur data jadwal tidak valid: {e}", level="ERROR")
        return None
    except ValueError as e:
        log_print(f"Format waktu dalam data jadwal tidak valid: {e}", level="ERROR")
        return None


def set_new_volume(volume):
    """
    Mengirim permintaan ke server untuk mengatur volume baru.
    """
    try:
        url = SET_VOLUME_URL.format(vol=volume)
        log_print(f"Mengatur volume menjadi {volume} melalui URL: {url}")
        response = requests.get(url, timeout=10)
        response.raise_for_status()
        log_print(f"Volume berhasil diatur. Respons server: {response.text}")
    except requests.exceptions.RequestException as e:
        log_print(f"Gagal mengatur volume: {e}", level="ERROR")


# ==============================================================================
# Logika Eksekusi Utama
# ==============================================================================
def main():
    """Fungsi utama untuk menjalankan loop polling."""
    log_print("Memulai skrip polling volume otomatis.")
    while True:
        log_print("Memulai siklus pengecekan baru...")
        
        # 1. Ambil data jadwal
        schedule = fetch_schedule_data()
        
        if schedule:
            # 2. Cari entri yang paling relevan
            entry = find_applicable_entry(schedule)
            
            if entry and 'vol' in entry:
                # 3. Atur volume jika entri ditemukan
                log_print(f"Entri yang berlaku ditemukan: {entry}")
                set_new_volume(entry['vol'])
            else:
                log_print("Tidak ada entri yang perlu diproses saat ini.")

        log_print(f"Siklus selesai. Menunggu selama {POLL_INTERVAL_SECONDS} detik.")
        time.sleep(POLL_INTERVAL_SECONDS)


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        log_print("Skrip dihentikan oleh pengguna (Ctrl+C). Selamat tinggal!")
