import time
import requests
import paho.mqtt.client as mqtt

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
        messages.append({'topic' : msg.topic, 'message' : msg.payload})

    else :
        try :
            filtered_message = [d for d in messages if d['topic'] == msg.topic][0]
            messages.remove(filtered_message)
        except :
            pass



client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(host, 1883, 60)

def millis():
    return round(time.time() * 1000)
    
time_before = 0
index = 0
while True :

    client.loop()

    if len(messages) > 0 :
        if millis() - time_before > 8000:
            
            try :
                id = messages[index]['topic'][-6:]
                filtered_list = [d for d in devices if d['id'] == id][0]

                str_kirim = filtered_list['username']

                if 'toilet' not in messages[index]['topic'] :
                    if b'e' in messages[index]['message'] :
                        str_kirim = str_kirim.replace('Ruang', 'Darurat')
                    elif b'i' in messages[index]['message'] :
                        str_kirim = str_kirim.replace('Ruang', 'Infus')
                    elif b'b' in messages[index]['message'] :
                        str_kirim = str_kirim.replace('Ruang', 'CodeBlue')
                    elif b'a' in messages[index]['message'] :
                        str_kirim = str_kirim.replace('Ruang', 'Perawat')

                print(filtered_list['username'])
                client.publish("dotmatrix", payload=str_kirim, qos=0, retain=False)

                index+=1
                if index >= len(messages):
                    index = 0

                time_before = millis()
            except Exception as e:
                print(e)
                index = 0
                pass
