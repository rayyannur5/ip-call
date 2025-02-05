import subprocess
import requests
import time
import paho.mqtt.client as mqtt

time.sleep(10)

client = mqtt.Client()
client.connect("localhost", 1883, 60)
client.loop_start()

beds = requests.get('http://localhost/ip-call/server/bed/get_all.php').json()['data']


def execute(command):
    return subprocess.run(command, capture_output=True, shell=True).stdout.decode()

while True:
    for bed in beds:
        check = execute(f"sudo asterisk -rx \"pjsip show endpoints\" | grep -i \"{bed['id']}\" | grep -i \"transport\"")
        if "transport" in check:
            print(f"{bed['id']} : registered")
        else :
            print(bed['id'])
            client.publish(bed['id'], payload='r', retain=1, qos=1)

    check = execute(f"sudo asterisk -rx \"pjsip show endpoints\" | grep -i \"server\" | grep -i \"transport\"")
    if "transport" in check:
        print(f"server : registered")
    else :
        print("server")
        client.publish("server", payload='r', retain=1, qos=1)

    print()
    time.sleep(5)

