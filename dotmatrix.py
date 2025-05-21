import time
import requests
import paho.mqtt.client as mqtt
from collections import defaultdict
import itertools

host = "localhost"

devices = []
messages = []

def on_connect(client, userdata, flags, rc):
    print("LOG| Connected with result code "+str(rc))
    x = requests.get(f'http://{host}/ip-call/server/device.php').json()

    for room in x['data']:
        # print(room['id'])
        for device in room['device']:
            # print(device)
            device['running_text'] = room['running_text']
        
            if 'room_id' in device:
                devices.append(device)
                if 'vol' in device:
                    # client.subscribe(f"stop/{device['id']}")
                    client.subscribe(f"infus/{device['id']}")
                    client.subscribe(f"bed/{device['id']}")
                    client.subscribe(f"assist/{device['id']}")
                else :
                    client.subscribe(f"toilet/{device['id']}")

            # if 'vol' in device :
            #     print(device)

def on_message(client, userdata, msg):
#     if first_on.is_set() :
    print(msg.topic+" "+str(msg.payload))
    
    if ('x' not in str(msg.payload) ) and ('c' not in str(msg.payload)):
        id = msg.topic[-6:]
        filtered_list = [d for d in devices if d['id'] == id][0]
        messages.append({'topic' : msg.topic, 'message' : msg.payload, 'running_text': filtered_list['running_text']})
        print(messages)

    else :
        try :
            filtered_message = [d for d in messages if d['topic'] == msg.topic][0]
            messages.remove(filtered_message)
        except :
            pass


time.sleep(10)
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(host, 1883, 60)

def millis():
    return round(time.time() * 1000)


def group_data(data_list):
    grouped = defaultdict(list)
    for item in data_list:
        grouped[item["running_text"]].append(item)
    return grouped

grouped_data = group_data(messages)
group_iterators = {g: itertools.cycle(v) for g, v in grouped_data.items()}
    
time_before = 0
index = 0

timeout = 10000

while True :

    client.loop()

    if len(messages) > 0:
        if millis() - time_before > timeout:

            utils = requests.get(f"http://{host}/ip-call/server/utils.php").json()['data']

            for util in utils:
                if util['type'] == 'timeout_running_text':
                    timeout = int(util['value'])

            try :

                new_grouped_data = group_data(messages)
    
                # Periksa apakah ada perubahan dalam jumlah grup atau elemen di grup
                if new_grouped_data.keys() != grouped_data.keys() or any(len(new_grouped_data[g]) != len(grouped_data[g]) for g in new_grouped_data):
                    grouped_data = new_grouped_data
                    group_iterators = {g: itertools.cycle(v) for g, v in grouped_data.items()}
                
                # Ambil data berikutnya dari setiap grup
                for group, iterator in group_iterators.items():
                    data = next(iterator)
                    # print(f"Group {group}: {data}")

                    id = data['topic'][-6:]
                    filtered_list = [d for d in devices if d['id'] == id][0]

                    str_kirim = filtered_list['username']

                    if 'toilet' not in data['topic'] :
                        if b'e' in data['message'] :
                            str_kirim = str_kirim.replace('Ruang', 'Darurat')
                        elif b'i' in data['message'] :
                            str_kirim = str_kirim.replace('Ruang', 'Infus')
                        elif b'b' in data['message'] :
                            str_kirim = str_kirim.replace('Ruang', 'CodeBlue')
                        elif b'a' in data['message'] :
                            str_kirim = str_kirim.replace('Ruang', 'Perawat')

                    print(filtered_list['username'] + ' ' + data['topic'])
                    if data['running_text'] != None:
                        running_text_data = requests.get(f"http://{host}/ip-call/server/running_text.php?id={data['running_text']}").json()
                        speed = str(running_text_data['speed']).rjust(3, '0')
                        brightness = str(running_text_data['brightness']).rjust(3, '0')
                        client.publish(data['running_text'], payload=speed + brightness + str_kirim, qos=0, retain=False)

                # index+=1
                # if index >= len(messages):
                #     index = 0

                time_before = millis()
            except Exception as e:
                print(e)
                index = 0
                pass
