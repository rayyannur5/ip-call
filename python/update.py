import requests
import sys

name = sys.argv[1]

print(name)

response = requests.get(f"http://localhost/ip-call/server/history/update.php?name={name}")

print(response.json())
