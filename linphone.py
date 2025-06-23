import subprocess
import time
import paho.mqtt.client as mqtt
from threading import Event

# ==============================================================================
# Global Variables & Configuration
# ==============================================================================
HOST = '127.0.0.1'
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
USERNAME = 'server'
PASSWORD = 'server'

# Threading events to manage state
is_connected = Event()
in_calling = Event()
reregister = Event()

# ==============================================================================
# Logging Function
# ==============================================================================
def log_print(message, level="INFO"):
    """
    Prints a log message with a timestamp and log level.
    e.g., [2025-06-23 09:59:00] [INFO] This is a message.
    """
    timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] [{level.upper()}] {message}")

# ==============================================================================
# Shell Command Execution
# ==============================================================================
def execute(command):
    """
    Executes a shell command and returns its stdout.
    Logs the command for debugging purposes.
    """
    log_print(f"Executing command: '{command}'", level="DEBUG")
    try:
        # Using text=True is the modern equivalent of .stdout.decode()
        result = subprocess.run(command, capture_output=True, shell=True, text=True, check=True)
        return result.stdout.strip()
    except subprocess.CalledProcessError as e:
        log_print(f"Error executing command: '{command}'", level="ERROR")
        log_print(f"Stderr: {e.stderr.strip()}", level="ERROR")
        return "" # Return empty string on error

def millis():
    """Returns the current time in milliseconds."""
    return round(time.time() * 1000)

# ==============================================================================
# MQTT Callbacks
# ==============================================================================
def on_connect(client, userdata, flags, rc):
    """Callback for when the client connects to the MQTT broker."""
    if rc == 0:
        log_print(f"Connected to MQTT Broker at {MQTT_BROKER}.")
        client.subscribe('panggil')
        client.subscribe('tutup')
        client.subscribe('server')
    else:
        log_print(f"Failed to connect to MQTT, return code {rc}", level="ERROR")

def on_message(client, userdata, msg):
    """Callback for when a PUBLISH message is received from the broker."""
    payload = msg.payload.decode()
    log_print(f"Received message on topic '{msg.topic}': {payload}")

    if not is_connected.is_set():
        log_print("Linphone not ready, ignoring message.", level="WARNING")
        return

    if msg.topic == 'panggil':
        # Don't dial if the payload is '1' (which indicates an outgoing call)
        if payload != '1':
            log_print(f"Initiating call to {payload}")
            execute(f'linphonecsh dial {payload}')
    
    elif msg.topic == 'tutup':
        log_print("Terminating current call.")
        execute('linphonecsh generic terminate')
        in_calling.clear()
    
    elif msg.topic == 'server':
        log_print("Received server command to re-register.")
        reregister.set()

# ==============================================================================
# Linphone Setup
# ==============================================================================
def setup_linphone():
    """Initializes and registers Linphone."""
    is_connected.clear()
    execute("linphonecsh init -c /home/nursecallserver/.config/linphone/linphonerc")
    log_print("Registering Linphone...")
    
    # Registration loop
    register_command = f"linphonecsh register --host {HOST} --username {USERNAME} --password {PASSWORD}"
    execute(register_command)
    
    start_time = millis()
    while "registered," not in execute("linphonecsh status register"):
        if millis() - start_time > 60000: # 60-second timeout
            log_print("Linphone registration timed out. Rebooting.", level="CRITICAL")
            execute("reboot")
            return # Exit function after reboot command
        log_print("Registration not yet successful, retrying...", level="WARNING")
        execute(register_command)
        time.sleep(2)
        
    log_print("Linphone registered successfully.")
    
    # Soundcard setup loop
    log_print("Setting up soundcard...")
    start_time = millis()
    soundcard_list = execute("linphonecsh generic 'soundcard list'")
    while "echo" not in soundcard_list:
        if millis() - start_time > 60000:
            log_print("Soundcard 'echo' not found. Rebooting.", level="CRITICAL")
            execute("reboot")
            return
        soundcard_list = execute("linphonecsh generic 'soundcard list'")
        time.sleep(1)
        
    for line in soundcard_list.split("\n"):
        if "echo" in line:
            try:
                soundcard_index = line.strip().split()[0]
                res = execute(f"linphonecsh generic 'soundcard use {soundcard_index}'")
                log_print(f"Soundcard set to index {soundcard_index}: {line.strip()}")
                break
            except (IndexError, ValueError) as e:
                log_print(f"Could not parse soundcard line: '{line}'. Error: {e}", level="ERROR")

    log_print("Linphone setup complete.")
    is_connected.set()

# ==============================================================================
# Main Execution Logic
# ==============================================================================
if __name__ == "__main__":
    log_print("Script starting...")
    
    # Initialize MQTT Client
    client = mqtt.Client()
    client.on_connect = on_connect
    client.on_message = on_message

    log_print("Waiting 10 seconds before initialization...")
    time.sleep(10)

    try:
        log_print(f"Connecting to MQTT broker at {MQTT_BROKER}:{MQTT_PORT}")
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
    except Exception as e:
        log_print(f"Fatal error connecting to MQTT: {e}", level="CRITICAL")
        exit() # Exit if we can't connect to the broker

    setup_linphone()
    client.loop_start()

    timer_5_seconds = millis()

    try:
        while True:
            # Publish a heartbeat every 5 seconds
            if millis() - timer_5_seconds > 5000:
                client.publish('internal', payload='1', retain=False, qos=1)
                timer_5_seconds = millis()
            
            # Check for new outgoing calls initiated by linphone
            if not in_calling.is_set():
                if 'duration' in execute('linphonecsh status hook'):
                    log_print("Outgoing call detected. Updating state.")
                    in_calling.set()
                    client.publish('panggil', '1') # Publish '1' to indicate we are busy

            # Check if a call has ended
            if in_calling.is_set():
                if 'on-hook' in execute('linphonecsh status hook'):
                    log_print("Call has ended. Clearing state.")
                    in_calling.clear()

            # Handle re-registration requests
            if reregister.is_set():
                log_print("Re-registering Linphone...")
                execute('linphonecsh unregister')
                setup_linphone()
                reregister.clear()
            
            time.sleep(0.2) # Small delay to prevent high CPU usage
    except KeyboardInterrupt:
        log_print("Shutdown signal received (Ctrl+C).")
    except Exception as e:
        log_print(f"An unexpected error occurred in the main loop: {e}", level="CRITICAL")
    finally:
        log_print("Stopping MQTT loop and shutting down.")
        client.loop_stop()
        execute('linphonecsh exit')
        log_print("Script finished.")
