# Bed Code Blue speak service.
# Listens on MQTT topic bed/{id}, and when a bed with mode==2 sends payload
# 'e', speaks "blue <username>" out of a USB soundcard on a repeating interval
# until the bed sends 'c'/'x' (clear). Port of message_controller.dart +
# audio_service.dart from ~/ip_call_desktop, narrowed to the Code Blue path.
#
# pip deps: requests paho-mqtt sounddevice soundfile
# system deps: libportaudio2 (sounddevice), libsndfile1 (soundfile)

import argparse
import re
import time

import numpy as np
import requests
import paho.mqtt.client as mqtt
import sounddevice as sd
import soundfile as sf

# ==============================================================================
# Global Variables & Configuration
# ==============================================================================
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
HOST = "localhost"  # HTTP base for /ip-call/server/*

SPEAKS_DIR = "/var/www/ip-call/python/speaks"  # copied from ip_call_desktop/assets/speaks
PUBLIC_DIR = "/var/www/ip-call/public"  # for mastersound-resolved static files

DEDUP_WINDOW_SECONDS = 1  # port of processingTopics (mqtt_service.dart)
WORD_GAP_MS = 100  # port of wordGap (audio_service.dart)
NUMBER_GAP_MS = 50  # port of numberGap (audio_service.dart)
DEFAULT_INTERVAL_SPEAKS_MS = 1000  # fallback if utils.php has no interval_speaks row
SPEAK_ROUND_ROBIN_REPEATS = 3  # port of counterIndexInterval >= 3 (message_controller.dart)
PLAYBACK_POLL_SECONDS = 0.02  # how often to check if a clip finished playing
PLAYBACK_TIMEOUT_MARGIN_SECONDS = 2.0  # extra time allowed past a clip's real duration before giving up
# Source clips in speaks/ peak around 0.4-0.6 instead of near 1.0, and playback
# goes straight to a raw ALSA hw: device (see _resample comment below), which
# bypasses the OS/software mixer entirely - so system "master volume" has no
# effect here. Clips are scaled so their peak would hit this value, then
# hard-clipped to [-1, 1] - intentionally >1.0 so quiet clips get overdriven
# for extra loudness rather than just filling headroom. Raise further for more
# volume (at the cost of more clipping distortion), or drop back to ~0.97 for
# a clean, undistorted ceiling. Set to 0 to disable and play clips unmodified.
PLAYBACK_TARGET_PEAK = 2

# Built-in catalog (verbatim from audio_service.dart's speakFiles + letter loop)
BUILTIN_WORDS = [
    "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh",
    "delapan", "sembilan", "sepuluh", "puluh", "sebelas", "belas",
    "darurat", "telepon", "infus", "blue", "tidak_terjawab", "perawat",
]
BUILTIN_LETTERS = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l"]

_letter_regex = re.compile(r"^[a-zA-Z]$")


# ==============================================================================
# Logging
# ==============================================================================
def log_print(message, level="INFO"):
    allowed_levels = ["info", "error", "critical"]
    if level.lower() in allowed_levels:
        timestamp = time.strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] [{level.upper()}] {message}")


def millis():
    return round(time.time() * 1000)


# ==============================================================================
# Sound catalog
# ==============================================================================
class SoundCatalog:
    def __init__(self, host):
        self._paths = {}
        self._load_builtin()
        self._load_dynamic(host)

    def _load_builtin(self):
        for name in BUILTIN_WORDS:
            self._paths[name] = f"{SPEAKS_DIR}/{name}.ogg"
        for letter in BUILTIN_LETTERS:
            self._paths[letter] = f"{SPEAKS_DIR}/{letter.upper()}.mp3"

    def _load_dynamic(self, host):
        try:
            res = requests.get(f"http://{host}/ip-call/server/sounds.php", timeout=5).json()
            for sound in res.get("data", []):
                name = (sound.get("name") or "").lower()
                source = sound.get("source")
                if not name or not source:
                    continue
                self._paths[name] = f"{PUBLIC_DIR}/{source}"
        except Exception as e:
            log_print(f"Failed to load dynamic sounds from sounds.php: {e}", level="ERROR")

    def get(self, key):
        return self._paths.get(key)


# ==============================================================================
# USB soundcard selection
# ==============================================================================
# Re-checked periodically (see AudioPlayerService._play_sound) so a card
# plugged in - or unplugged - mid-run is picked up without a restart. Only
# log on state change, otherwise this would spam the log on every word played.
DEVICE_REFRESH_SECONDS = 2.0  # how often to actually re-scan for hardware changes

_UNSET = object()
_last_logged_device = _UNSET
_cached_device_index = None
_last_refresh_at = 0.0


def select_usb_playback_device():
    global _last_logged_device, _cached_device_index, _last_refresh_at

    now = time.time()
    if now - _last_refresh_at < DEVICE_REFRESH_SECONDS:
        return _cached_device_index
    _last_refresh_at = now

    try:
        # PortAudio caches its device list at init and does not notice
        # hotplug add/remove on its own - force a re-scan so an unplugged
        # card is actually seen as gone (and a newly plugged one is actually
        # seen as present), instead of playing against stale, now-invalid
        # device info. Rate-limited to DEVICE_REFRESH_SECONDS (rather than
        # once per word) because repeated Pa_Terminate/Pa_Initialize cycles
        # measurably leak native memory over time.
        sd._terminate()
        sd._initialize()
        devices = sd.query_devices()
    except Exception as e:
        if _last_logged_device is not None:
            log_print(f"Failed to query audio devices: {e}", level="CRITICAL")
            _last_logged_device = None
        _cached_device_index = None
        return None

    for index, device in enumerate(devices):
        if device.get("max_output_channels", 0) > 0 and "usb" in device.get("name", "").lower():
            if _last_logged_device != device["name"]:
                log_print(f"Selected USB playback device [{index}]: {device['name']}")
                _last_logged_device = device["name"]
            _cached_device_index = index
            return index

    if _last_logged_device is not None:
        log_print("No USB playback device found, audio will be skipped until one is detected.", level="CRITICAL")
        _last_logged_device = None
    _cached_device_index = None
    return None


# ==============================================================================
# Audio playback (port of audio_service.dart, minus dot-matrix)
# ==============================================================================
class AudioPlayerService:
    def __init__(self, catalog: SoundCatalog):
        self.catalog = catalog

    @staticmethod
    def _query_device_samplerate(device_index):
        if device_index is None:
            return None
        try:
            return int(round(sd.query_devices(device_index)["default_samplerate"]))
        except Exception as e:
            log_print(f"Failed to query samplerate for device {device_index}: {e}", level="ERROR")
            return None

    @staticmethod
    def _resample(data, orig_sr, target_sr):
        # Raw ALSA hw: devices (bypassing the ALSA "plug" layer) only accept
        # the exact rate the hardware natively runs at, so clips recorded at
        # a different rate must be resampled ourselves before playback.
        if orig_sr == target_sr:
            return data
        orig_len = data.shape[0]
        target_len = max(1, int(round(orig_len * target_sr / orig_sr)))
        orig_idx = np.linspace(0, orig_len - 1, num=orig_len)
        target_idx = np.linspace(0, orig_len - 1, num=target_len)
        if data.ndim == 1:
            return np.interp(target_idx, orig_idx, data)
        return np.column_stack([np.interp(target_idx, orig_idx, data[:, ch]) for ch in range(data.shape[1])])

    def _play_sound(self, key):
        path = self.catalog.get(key)
        if path is None:
            return
        # Re-detect on every clip rather than once at startup, so a USB
        # soundcard plugged in mid-run gets picked up without a restart.
        device_index = select_usb_playback_device()
        if device_index is None:
            log_print(f"Skipping sound '{key}': no USB playback device detected.", level="ERROR")
            return

        try:
            device_samplerate = self._query_device_samplerate(device_index)

            data, samplerate = sf.read(path)
            if PLAYBACK_TARGET_PEAK:
                peak = np.max(np.abs(data)) if data.size else 0
                if peak > 0:
                    data = np.clip(data * (PLAYBACK_TARGET_PEAK / peak), -1.0, 1.0)
            if data.ndim == 1:
                # Source clips are mono, but this plays straight to a raw ALSA
                # hw: device (see _resample comment below) with no "plug" layer
                # to remap channels - a 1-channel stream isn't guaranteed to
                # come out of both physical outputs. Duplicate to stereo so it
                # always does.
                data = np.column_stack([data, data])
            if device_samplerate is not None and samplerate != device_samplerate:
                data = self._resample(data, samplerate, device_samplerate)
                samplerate = device_samplerate

            duration_seconds = data.shape[0] / samplerate
            deadline = time.time() + duration_seconds + PLAYBACK_TIMEOUT_MARGIN_SECONDS

            sd.play(data, samplerate, device=device_index)
            # Not sd.wait(): if the device is unplugged mid-playback, PortAudio's
            # callback thread can die without ever signalling "finished", and
            # sd.wait() would block forever. Poll with our own deadline instead,
            # so a vanished device times out rather than hanging the whole loop.
            stream = sd.get_stream()
            while stream is not None and stream.active:
                if time.time() > deadline:
                    log_print(
                        f"Playback of '{key}' did not finish within {duration_seconds + PLAYBACK_TIMEOUT_MARGIN_SECONDS:.1f}s "
                        "(USB device likely disconnected mid-playback), aborting.",
                        level="ERROR",
                    )
                    sd.stop()
                    return
                time.sleep(PLAYBACK_POLL_SECONDS)
        except Exception as e:
            log_print(f"Failed to play sound '{key}' ({path}): {e}", level="ERROR")

    @staticmethod
    def numbers_to_text(num):
        def get_text(one_num):
            return {
                0: "kosong", 1: "satu", 2: "dua", 3: "tiga", 4: "empat",
                5: "lima", 6: "enam", 7: "tujuh", 8: "delapan", 9: "sembilan",
            }.get(one_num, "")

        puluhan = num // 10
        satuan = num % 10

        if num == 10:
            return "sepuluh"
        if num == 11:
            return "sebelas"
        if puluhan == 1 and satuan > 1:
            return f"{get_text(satuan)} belas"
        if puluhan != 0 and satuan != 0:
            return f"{get_text(puluhan)} puluh {get_text(satuan)}"
        if puluhan != 0 and satuan == 0:
            return f"{get_text(puluhan)} puluh"
        return get_text(satuan)

    @staticmethod
    def _is_letter(word):
        return bool(_letter_regex.match(word))

    def speak(self, display_str, msg_code, username):
        # Called once per tick from the single main loop (see __main__), so
        # there's never a concurrent speak() in flight to guard against.
        log_print(f"Speaking: '{display_str}' (msg={msg_code}, username={username})")
        try:
            words = display_str.lower().split(" ")
            last_index = len(words) - 1

            for i, word in enumerate(words):
                if self._is_letter(word):
                    self._play_sound(word)
                elif i == last_index and word.isdigit():
                    number_words = self.numbers_to_text(int(word)).split(" ")
                    for j, val in enumerate(number_words):
                        self._play_sound(val)
                        if j < len(number_words) - 1:
                            time.sleep(NUMBER_GAP_MS / 1000)
                else:
                    self._play_sound(word)

                if i < last_index:
                    time.sleep(WORD_GAP_MS / 1000)
        except Exception as e:
            log_print(f"Speak error: {e}", level="ERROR")


# ==============================================================================
# Message queue + round robin (port of message_controller.dart)
# ==============================================================================
def get_category_message(msg_code):
    return {
        "e": "darurat",
        "w": "telepon",
        "b": "blue",
        "0": "tidak_terjawab",
        "a": "perawat",
    }.get(msg_code, "infus")


class MessageController:
    """
    Single-threaded port of message_controller.dart's queue + round robin.
    add_message()/remove_message() only mutate state; the actual speaking
    happens in loop_speak(), which the main loop calls every iteration
    (same style as dotmatrix.py's millis()-based timeout check). Because
    everything - MQTT callbacks and speaking - runs on one thread via the
    manual client.loop() call in __main__, there's no concurrency to guard
    against: no lock, no timer, no thread needed.
    """

    def __init__(self, audio_service: AudioPlayerService, interval_speaks_ms):
        self.audio_service = audio_service
        self.interval_speaks_ms = interval_speaks_ms
        self.messages = []
        self._index_interval = 0
        self._counter_index_interval = 0
        self._last_speak_at = 0  # millis(); 0 forces an immediate speak on the next tick

    def add_message(self, topic, message, username):
        for item in self.messages:
            if item["topic"] == topic and item["message"] == message:
                return  # duplicate, ignore (port of addMessage dedup)
        self.messages.append({"topic": topic, "message": message, "username": username})
        log_print(f"Message queued: {topic} -> {message} ({username})")
        self._index_interval = 0
        self._counter_index_interval = 0
        self._last_speak_at = 0  # speak immediately instead of waiting out the interval

    def remove_message(self, topic):
        before = len(self.messages)
        self.messages = [m for m in self.messages if m["topic"] != topic]
        if len(self.messages) != before:
            log_print(f"Message for topic {topic} removed from queue.")

    def loop_speak(self):
        if not self.messages:
            return
        if millis() - self._last_speak_at < self.interval_speaks_ms:
            return
        self._last_speak_at = millis()

        if self._index_interval >= len(self.messages):
            self._index_interval = 0
            self._counter_index_interval = 0

        current = self.messages[self._index_interval]
        display_str = f"{get_category_message(current['message'])} {current['username']}"
        self.audio_service.speak(display_str, current["message"], current["username"])

        self._counter_index_interval += 1
        if self._counter_index_interval >= SPEAK_ROUND_ROBIN_REPEATS:
            self._counter_index_interval = 0
            self._index_interval += 1
            if self._index_interval >= len(self.messages):
                self._index_interval = 0


# ==============================================================================
# HTTP helpers
# ==============================================================================
def fetch_interval_speaks(host):
    # try:
    #     res = requests.get(f"http://{host}/ip-call/server/utils.php", timeout=5).json()
    #     for util in res.get("data", []):
    #         if util.get("type") == "interval_speaks":
    #             return int(util["value"])
    # except Exception as e:
    #     log_print(f"Failed to fetch interval_speaks from utils.php: {e}", level="ERROR")
    return DEFAULT_INTERVAL_SPEAKS_MS


def fetch_bed_mode_and_username(host, bed_id):
    res = requests.get(f"http://{host}/ip-call/server/bed/get_one.php", params={"id": bed_id}, timeout=5).json()
    data = res.get("data") or []
    if not data:
        return None, None
    bed = data[0]
    return bed.get("mode"), bed.get("username")


# ==============================================================================
# MQTT dedup (port of processingTopics in mqtt_service.dart)
# ==============================================================================
_processing = {}


def is_duplicate(topic, payload):
    key = f"{topic}-{payload}"
    now = time.time()

    # Prune anything past its window every call - without this, _processing
    # only ever grows (the broker allows anonymous publish, so nothing stops
    # unbounded distinct topic/payload combinations from accumulating here
    # forever). Keeps the dict bounded to "keys seen within the last
    # DEDUP_WINDOW_SECONDS", which is all we actually need.
    for stale_key in [k for k, exp in _processing.items() if exp <= now]:
        del _processing[stale_key]

    expires_at = _processing.get(key)
    if expires_at is not None and expires_at > now:
        return True
    _processing[key] = now + DEDUP_WINDOW_SECONDS
    return False


# ==============================================================================
# MQTT callbacks
# ==============================================================================
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        log_print(f"Connected to MQTT Broker at {MQTT_BROKER}.")
        client.subscribe("bed/+")
    else:
        log_print(f"Failed to connect to MQTT, return code {rc}", level="ERROR")


def make_on_message(host, message_controller: MessageController):
    def on_message(client, userdata, msg):
        topic = msg.topic
        payload = msg.payload.decode(errors="ignore")

        if is_duplicate(topic, payload):
            return

        bed_id = topic.split("/")[-1]

        if payload in ("c", "x"):
            message_controller.remove_message(topic)
            return

        if payload != "e":
            return

        try:
            mode, username = fetch_bed_mode_and_username(host, bed_id)
        except Exception as e:
            log_print(f"Failed to fetch bed {bed_id} for mode check: {e}", level="ERROR")
            return

        if mode is None:
            return

        if str(mode) != "2":
            return  # out of scope for this daemon - only Code Blue is announced

        message_controller.add_message(topic, "b", username or "")
        log_print(f"Code Blue triggered for bed {bed_id} ({username}).")

    return on_message


# ==============================================================================
# Argument parsing
# ==============================================================================
def parse_arguments():
    parser = argparse.ArgumentParser(description="Bed Code Blue Speak Service")
    parser.add_argument("--host", type=str, default=HOST, help=f"MQTT + HTTP host (default: {HOST})")
    parser.add_argument("--interval-speaks", type=int, default=None, help="Override interval_speaks in ms (skips utils.php fetch)")
    return parser.parse_args()


# ==============================================================================
# Main
# ==============================================================================
if __name__ == "__main__":
    args = parse_arguments()
    host = args.host

    log_print("Script starting...")
    log_print("Waiting 10 seconds before initialization...")
    time.sleep(10)

    catalog = SoundCatalog(host)
    interval_speaks_ms = args.interval_speaks if args.interval_speaks is not None else fetch_interval_speaks(host)
    log_print(f"Using interval_speaks = {interval_speaks_ms}ms")

    audio_service = AudioPlayerService(catalog)
    message_controller = MessageController(audio_service, interval_speaks_ms)

    client = mqtt.Client()
    client.on_connect = on_connect
    client.on_message = make_on_message(host, message_controller)

    try:
        log_print(f"Connecting to MQTT broker at {MQTT_BROKER}:{MQTT_PORT}")
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
    except Exception as e:
        log_print(f"Fatal error connecting to MQTT: {e}", level="CRITICAL")
        raise SystemExit(1)

    try:
        while True:
            client.loop()  # processes MQTT I/O + fires on_connect/on_message, ~1s internal timeout
            message_controller.loop_speak()  # blocking; runs after loop() so pending messages are fresh
    except KeyboardInterrupt:
        log_print("Shutdown signal received (Ctrl+C).")
    finally:
        log_print("Script finished.")
