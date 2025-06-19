import subprocess
import time
import paho.mqtt.client as mqtt
from threading import Event
import requests

# Global variables
host = '127.0.0.1'
username = 'server'
password = 'server'
isconnected = Event()
inCalling = Event()
reregister = Event()

# The callback for when the client receives a CONNACK response from the server.
def on_connect(client, userdata, flags, rc):
    print("LOG| Connected with result code "+str(rc))

    client.subscribe('panggil')
    client.subscribe('tutup')
    client.subscribe('server')

# The callback for when a PUBLISH message is received from the server.
# first_on = Event()
def on_message(client, userdata, msg):
#     if first_on.is_set() :
    if isconnected.is_set():
        if msg.topic == 'panggil':
            if msg.payload.decode() != 1:
                execute(f'linphonecsh dial {msg.payload.decode()}')
        
        if msg.topic == 'tutup':
            execute('linphonecsh generic terminate')
            inCalling.clear()
    
    if msg.topic == 'server':
        reregister.set()
    print(msg.topic+" "+msg.payload.decode())

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

def execute(command):
    return subprocess.run(command, capture_output=True, shell=True).stdout.decode()

def millis():
    return round(time.time() * 1000)

def setupLinphone():
    execute("linphonecsh init -c /home/rayyan/.config/linphone/linphonerc")
    print("LOG| LINPHONE REGISTERING")
    execute(f"linphonecsh register --host {host} --username {username} --password {password}")
    res = execute("linphonecsh status register")
    before_linphone = millis()
    while "registered," not in res:
        execute(f"linphonecsh register --host {host} --username {username} --password {password}")
        res = execute("linphonecsh status register")
        time.sleep(0.1)
        time.sleep(0.1)
        # if millis() - before_linphone >60000:
        #     execute("reboot")
    
    res = execute("linphonecsh generic 'soundcard list'")
    before_linphone = millis()
    while "echo" not in res:
        if millis() - before_linphone > 60000:
            execute("reboot")
        res = execute("linphonecsh generic 'soundcard list'")
        
    res = res.split("\n")
    for i in res:
        if "echo" in i:
            index_soundcard = i[0]
            res = execute(f"linphonecsh generic 'soundcard use {index_soundcard}'")
            print(f"LOG| {res}")
            break
    
    print("LOG| LINPHONE REGISTERED")
    isconnected.set()

time.sleep(10)
client.connect("localhost", 1883, 60)
setupLinphone()
client.loop_start()

timer5detik = 0

while True:
    if millis() - timer5detik > 5000:
        client.publish('internal', payload='1', retain=False, qos=1)
        timer5detik = millis()
    
    if not inCalling.is_set():
        res = execute('linphonecsh status hook')
        if 'duration' in res:
            inCalling.set()
            client.publish('panggil', 1)

    res = execute('linphonecsh status hook')
    if 'on-hook' in res:
        inCalling.clear()

    if reregister.is_set():
        execute('linphonecsh unregister')
        setupLinphone()
        reregister.clear()
