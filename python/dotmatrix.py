import time
import requests
import paho.mqtt.client as mqtt
from collections import defaultdict
import datetime
import gc

# --- Fungsi Logging Kustom ---
def log_print(*args, **kwargs):
    """Fungsi print kustom yang menambahkan timestamp di awal pesan."""
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    log_prefix = f"[{timestamp}]"
    print(log_prefix, *args, **kwargs)

# --- Konfigurasi Awal ---
host = "localhost"
devices = []
messages = []
subscribed_topics = set()
session = requests.Session()

# --- Fungsi Callback MQTT ---
def on_connect(client, userdata, flags, rc):
    log_print("LOG| Connected with result code " + str(rc))
    subscribed_topics.clear() # Reset langganan saat reconnect agar subscribe ulang
    resubscribe()
    
def on_message(client, userdata, msg):
    """
    Callback function untuk menangani pesan MQTT yang masuk.
    Tetap menggunakan list dan mencegah duplikasi (topik dan isi pesan yang sama).
    """
    # log_print(f"Pesan diterima - Topik: {msg.topic}, Payload: {str(msg.payload)}")

    # Bagian untuk menghapus pesan dari list
    if 'x' in str(msg.payload) or 'c' in str(msg.payload):
        try:
            # Mencari pesan yang akan dihapus berdasarkan topik.
            # Ini akan menghapus pesan pertama yang cocok dengan topik.
            item_to_remove = next(d for d in messages if d['topic'] == msg.topic)
            messages.remove(item_to_remove)
            log_print(f"Pesan untuk topik {msg.topic} dihapus dari antrian.")
        except StopIteration:
            # Jika pesan tidak ditemukan, tidak melakukan apa-apa.
            pass
            # log_print(f"Pesan untuk topik {msg.topic} tidak ditemukan untuk dihapus.")
        
        # log_print(f"Antrian saat ini: {messages}")
        return

    # Bagian untuk menambahkan pesan ke list (dengan pengecekan duplikat)
    try:
        device_id = msg.topic[-6:]
        # Mencari info device dengan aman (tidak akan error jika tidak ketemu)
        device_info = next((d for d in devices if d['id'] == device_id), None)

        if not device_info:
            log_print(f"Error: Device dengan ID {device_id} tidak ditemukan.")
            return

        # Tentukan payload (isi pesan) final yang akan disimpan
        final_payload = msg.payload
        if 'mode' in device_info and device_info['mode'] == '2' and msg.payload == b'e':
            final_payload = b'b'

        # *** INI BAGIAN PENTINGNYA ***
        # Cek apakah pesan dengan topik dan payload yang sama sudah ada di dalam list
        is_duplicate = False
        for item in messages:
            if item['topic'] == msg.topic and item['message'] == final_payload:
                is_duplicate = True
                break
        
        if not is_duplicate:
            # Jika bukan duplikat, buat pesan baru dan tambahkan ke list
            new_message = {
                'topic': msg.topic,
                'message': final_payload,
                'running_text': device_info.get('running_text', '')
            }
            messages.append(new_message)
            log_print(f"Pesan baru ditambahkan: {new_message}")
        else:
            pass
            # Jika duplikat, abaikan pesan tersebut
            # log_print(f"Pesan duplikat diabaikan: Topik={msg.topic}")

    except Exception as e:
        log_print(f"Terjadi error saat memproses pesan: {e}")

    # log_print(f"Antrian saat ini: {messages}")

def resubscribe():
    global devices
    try:
        # Menggunakan session agar koneksi TCP di-reuse (hemat resource)
        x = session.get(f'http://{host}/ip-call/server/device.php').json()
        
        # Buat list device baru sementara
        new_devices = []
        current_needed_topics = set()
        
        for room in x['data']:
            for device in room['device']:
                device['running_text'] = room['running_text']
                
                if 'room_id' in device:
                    new_devices.append(device)
                    # Kumpulkan topik yang PERLU disubscribe kali ini
                    if 'vol' in device:
                        current_needed_topics.add(f"infus/{device['id']}")
                        current_needed_topics.add(f"bed/{device['id']}")
                        current_needed_topics.add(f"assist/{device['id']}")
                    else:
                        current_needed_topics.add(f"toilet/{device['id']}")
        
        # Update list devices global
        devices = new_devices
        
        # Hitung topik baru yang belum disubscribe
        to_subscribe = current_needed_topics - subscribed_topics
        for topic in to_subscribe:
            client.subscribe(topic)
            subscribed_topics.add(topic)
            # log_print(f"Subscribed to new topic: {topic}")
            
        # Hitung topik lama yang tidak lagi diperlukan (opsional - agar rapi)
        to_unsubscribe = subscribed_topics - current_needed_topics
        for topic in to_unsubscribe:
            client.unsubscribe(topic)
            subscribed_topics.remove(topic)
            # log_print(f"Unsubscribed from topic: {topic}")

    except requests.exceptions.RequestException as e:
        log_print(f"LOG| Tidak bisa terhubung ke server untuk mengambil data device: {e}")

# --- Fungsi Bantuan ---
def millis():
    return round(time.time() * 1000)

def group_data(data_list):
    grouped = defaultdict(list)
    for item in data_list:
        grouped[item["running_text"]].append(item)
    return dict(grouped) # Ubah ke dict biasa agar lebih aman

# --- Inisialisasi MQTT Client ---
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(host, 1883, 60)

# =================================================================
# INI ADALAH STATE UNTUK MENYIMPAN POSISI SETIAP GRUP
# Format: {'nama_grup_1': 0, 'nama_grup_2': 2}
group_posisi = {}
# =================================================================

time_before = 0
timeout = 10000 # Default timeout 10 detik

# Nilai counter untuk Garbage Collection berkala
gc_counter = 0

# --- Loop Utama Program ---
while True:
    client.loop()

    if millis() - time_before > timeout:
        time_before = millis()
        
        # Jalankan GC setiap 10 siklus timeout (approx 100 detik) untuk membersihkan memori
        gc_counter += 1
        if gc_counter >= 10:
            gc.collect()
            gc_counter = 0

        # Jika tidak ada pesan, cek resubscribe (update config device) lalu continue
        if not messages:
            # Kosongkan juga state posisi jika tidak ada pesan
            group_posisi.clear()
            resubscribe()
            continue

        try:
            # Menggunakan session
            utils = session.get(f"http://{host}/ip-call/server/utils.php").json()['data']

            for util in utils:
                if util['type'] == 'timeout_running_text':
                    timeout = int(util['value'])

            # 1. Ambil data grup yang paling baru dari list `messages`
            grouped_data = group_data(messages)
            
            # (Opsional tapi bagus) Bersihkan state untuk grup yang sudah tidak ada
            current_groups = set(grouped_data.keys())
            known_groups = set(group_posisi.keys())
            for group_to_remove in known_groups - current_groups:
                del group_posisi[group_to_remove]

            # 2. Proses setiap grup yang aktif saat ini
            for group_name, items_in_group in grouped_data.items():
                
                # 3. Dapatkan posisi saat ini untuk grup ini. Jika grup baru, mulai dari 0.
                posisi_sekarang = group_posisi.get(group_name, 0)

                # 4. Jaga-jaga jika jumlah pesan berkurang dan posisi jadi tidak valid
                if posisi_sekarang >= len(items_in_group):
                    posisi_sekarang = 0
                
                # 5. Ambil data yang akan dikirim berdasarkan posisi saat ini
                data = items_in_group[posisi_sekarang]
                
                # --- Logika untuk publish pesan (sama seperti sebelumnya) ---
                id = data['topic'][-6:]
                # Gunakan pendekatan lebih aman agar tidak crash jika device hilang
                try:
                    filtered_list = next(d for d in devices if d['id'] == id)
                    str_kirim = filtered_list['username']
                except StopIteration:
                    # Device mungkin telah dihapus tapi ada di antrian pesan
                    continue

                if 'toilet' not in data['topic']:
                    # Gunakan 'final_payload' yang sudah ditentukan di on_message
                    if data['message'] == b'e':
                        str_kirim = str_kirim.replace('Ruang', 'Darurat')
                    elif data['message'] == b'i':
                        str_kirim = str_kirim.replace('Ruang', 'Infus')
                    elif data['message'] == b'b':
                        str_kirim = str_kirim.replace('Ruang', 'CodeBlue')
                    elif data['message'] == b'a':
                        str_kirim = str_kirim.replace('Ruang', 'Perawat')
                

                if data['running_text'] != None and data['running_text'] != '':
                    # Ambil setting speed & brightness via session
                    # Hati-hati: running_text.php mungkin perlu id yang valid
                    try:
                        running_text_data = session.get(f"http://{host}/ip-call/server/running_text.php?id={data['running_text']}").json()
                        speed = str(running_text_data['speed']).rjust(3, '0')
                        brightness = str(running_text_data['brightness']).rjust(3, '0')
                        client.publish(data['running_text'], payload=speed + brightness + str_kirim, qos=0, retain=False)
                        log_print(f"PUBLISH| Grup: '{group_name}', Item ke-{posisi_sekarang}: {str_kirim}")
                    except Exception as e_req:
                        log_print(f"Error fetching running text data: {e_req}")
                
                # 6. Hitung posisi untuk putaran BERIKUTNYA dan simpan ke state
                posisi_berikutnya = (posisi_sekarang + 1) % len(items_in_group)
                group_posisi[group_name] = posisi_berikutnya
            
            resubscribe()

        except Exception as e:
            log_print(f"ERROR| Terjadi kesalahan di loop utama: {e}")
            pass
