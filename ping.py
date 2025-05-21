import paho.mqtt.client as mqtt
import time

host = "localhost"

client = mqtt.Client()
client.connect(host, 1883, 60)


client.loop_start()

while True :
    client.publish('ping', 'p', qos=0, retain=False)
    time.sleep(30)