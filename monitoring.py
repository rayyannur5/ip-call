import time
import glob
import os
import subprocess
from flask import Flask, render_template
from flask_socketio import SocketIO

# ==============================================================================
# --- KONFIGURASI UTAMA ---
# Cukup edit daftar ini untuk menambah atau menghapus skrip yang ingin Anda pantau.
# ==============================================================================
SCRIPTS_TO_MONITOR = [
    'dotmatrix.py',
    'linphone.py',
    'hour_audio.py',
    'ping.py',
    'fd.py',
    'monitoring.py'
]

# Inisialisasi Aplikasi Flask
app = Flask(__name__)
socketio = SocketIO(app)
LOG_FOLDER = '/opt/lampp/htdocs/ip-call/logs'

# --- FUNGSI BARU: Untuk membaca N baris terakhir dari file ---
def read_last_lines(filepath, num_lines=100):
    """Membaca sejumlah baris terakhir dari file secara efisien."""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            # Membaca semua baris, lalu ambil N terakhir. Cukup efisien untuk file log ukuran wajar.
            # Untuk file yang sangat besar (Gigabyte), pendekatan lain mungkin diperlukan.
            lines = f.readlines()
            return [line.strip() for line in lines[-num_lines:]]
    except FileNotFoundError:
        return []
    except Exception as e:
        print(f"Error saat membaca baris terakhir dari {filepath}: {e}")
        return []

def check_process_status(script_name):
    """
    Menjalankan 'ps aux' untuk memeriksa apakah sebuah proses skrip sedang berjalan.
    Mengembalikan True jika berjalan, False jika tidak.
    CATATAN: Perintah ini spesifik untuk Linux/macOS.
    """
    try:
        result = subprocess.run(['ps', 'aux'], capture_output=True, text=True, check=True)
        for line in result.stdout.splitlines():
            if script_name in line and 'python' in line.lower() and 'grep' not in line:
                return True
        return False
    except FileNotFoundError:
        print(f"PERINGATAN: Perintah 'ps' tidak ditemukan. Pengecekan status untuk '{script_name}' dilewati.")
        return False
    except Exception as e:
        print(f"Error saat menjalankan 'ps aux' untuk '{script_name}': {e}")
        return False

@app.route('/')
def index():
    """
    Menyajikan halaman web utama (index.html).
    Mengirimkan data awal dari semua skrip yang dipantau ke template.
    """
    scripts_data = []
    for script_name in sorted(SCRIPTS_TO_MONITOR):
        log_file = script_name.replace('.py', '.txt')
        log_path = os.path.join(LOG_FOLDER, log_file)
        has_log = os.path.exists(log_path)
        is_running = check_process_status(script_name)
        
        # --- PERUBAHAN: Ambil 100 baris terakhir jika log ada ---
        initial_logs = []
        if has_log:
            initial_logs = read_last_lines(log_path, 100)

        scripts_data.append({
            'name': script_name,
            'log_file': log_file,
            'has_log': has_log,
            'is_running': is_running,
            'initial_logs': initial_logs # Kirim log awal ke template
        })
        
    print(f"Menyajikan dasbor untuk skrip: {[s['name'] for s in scripts_data]}")
    return render_template('index.html', scripts=scripts_data)

def watch_and_report_background_task():
    """
    Tugas latar belakang yang berjalan terus-menerus untuk:
    1. Mengawasi perubahan file log.
    2. Memeriksa status proses secara berkala.
    """
    log_files_state = {}
    last_status_check = 0
    status_check_interval = 5

    print("--- Tugas Latar Belakang Dimulai: Mengawasi log dan status proses ---")

    while True:
        # BAGIAN 1: Mengawasi File Log
        try:
            for script_name in SCRIPTS_TO_MONITOR:
                log_path = os.path.join(LOG_FOLDER, script_name.replace('.py', '.txt'))
                if not os.path.exists(log_path):
                    continue

                if log_path not in log_files_state:
                    file = open(log_path, 'r', encoding='utf-8', errors='ignore')
                    # --- PERUBAHAN: Langsung ke akhir file saat memulai pengawasan ---
                    file.seek(0, 2)
                    log_files_state[log_path] = file
                
                file = log_files_state[log_path]
                line = file.readline()
                
                if line:
                    filename = os.path.basename(log_path)
                    socketio.emit('new_log_line', {'file': filename, 'data': line.strip()})
        except Exception as e:
            print(f"Error saat mengawasi log: {e}")
            for file in log_files_state.values(): file.close()
            log_files_state = {}
            socketio.sleep(2)

        # BAGIAN 2: Memeriksa Status Proses
        current_time = time.time()
        if current_time - last_status_check > status_check_interval:
            statuses = {script: check_process_status(script) for script in SCRIPTS_TO_MONITOR}
            socketio.emit('process_status_update', statuses)
            last_status_check = current_time

        socketio.sleep(0.5)

@socketio.on('connect')
def handle_connect():
    print('Client terhubung ke WebSocket.')

@socketio.on('disconnect')
def handle_disconnect():
    print('Client terputus dari WebSocket.')

if __name__ == '__main__':
    print("--- Menjalankan Server Dasbor Monitor ---")
    socketio.start_background_task(target=watch_and_report_background_task)
    socketio.run(app, host='0.0.0.0', port=5000, debug=False, allow_unsafe_werkzeug=True)
