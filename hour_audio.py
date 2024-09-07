import requests
import time
from datetime import datetime

def millis():
    return round(time.time() * 1000)

while True:

    response = requests.get('http://localhost/ip-call/server/hour/get.php').json()
    print(response)
    yr, month, day, hr, minute = map(int, time.strftime("%Y %m %d %H %M").split())
    print("now : " + str(hr) + ":" + str(minute))

    reference_time = str(hr) + ":" + str(minute) + ":00"

    array_input = response['data']

    format = "%H:%M:%S"  # Format untuk waktu dalam array
    ref_time = datetime.strptime(reference_time, format)
    
    # Filter waktu yang lebih kecil dari reference_time
    smaller_times = [item['time'] for item in array_input if datetime.strptime(item['time'], format) < ref_time]
    
    # Jika tidak ada waktu yang lebih kecil dari reference_time
    if not smaller_times:
        time.sleep(30)
        continue
    
    # Mengambil waktu terbesar dari yang lebih kecil
    largest_smaller_time = max(smaller_times, key=lambda x: datetime.strptime(x, format))
    
    # Mencari objek dalam array yang memiliki waktu tersebut
    result_obj = next((item for item in array_input if item['time'] == largest_smaller_time), None)

    print(result_obj['vol'])
    response = requests.get('http://localhost/ip-call/server/hour/set.php?vol=' + result_obj['vol'])
    # print(msg.topic+" "+str(msg.payload))

    time.sleep(30)
