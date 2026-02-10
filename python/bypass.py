import paho.mqtt.client as mqtt
import time

time.sleep(10)

host = "localhost"

client = mqtt.Client()
client.connect(host, 1883, 60)


client.loop_start()

while True :
    client.publish('aktif', '020101', qos=0, retain=False)
    client.publish('aktif', '020201', qos=0, retain=False)
    client.publish('aktif', '020301', qos=0, retain=False)
    client.publish('aktif', '020401', qos=0, retain=False)
    client.publish('aktif', '020501', qos=0, retain=False)
    client.publish('aktif', '020601', qos=0, retain=False)
    client.publish('aktif', '020701', qos=0, retain=False)
    client.publish('aktif', '020801', qos=0, retain=False)
    client.publish('aktif', '1', qos=0, retain=False)
    client.publish('aktif', '2', qos=0, retain=False)
    client.publish('aktif', '3', qos=0, retain=False)
    client.publish('aktif', '4', qos=0, retain=False)
    client.publish('aktif', '5', qos=0, retain=False)
    client.publish('aktif', '6', qos=0, retain=False)
    client.publish('aktif', '7', qos=0, retain=False)
    client.publish('aktif', '8', qos=0, retain=False)
    time.sleep(10)