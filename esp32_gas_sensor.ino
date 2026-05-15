/*
 * ESP32 SIARGO Gas Flow Sensor
 * ============================
 * - Baca data Flow & Accumulation via RS485 Modbus RTU
 * - Kirim ke server via HTTP POST setiap 5 detik
 * - Web server untuk clear accumulation
 * 
 * Wiring ESP32 -> RS485 Module:
 *   GPIO16 (RX2) -> RO
 *   GPIO17 (TX2) -> DI
 *   GPIO4        -> DE (Driver Enable)
 *   GPIO5        -> RE (Receiver Enable, active LOW)
 *   
 * Library yang dibutuhkan:
 *   - ModbusMaster (by Doc Walker)
 *   - ArduinoJson
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <ModbusMaster.h>
#include <ArduinoJson.h>

// ==================== KONFIGURASI ====================
// WiFi Station Mode
const char* WIFI_SSID = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// WiFi AP Mode (fallback jika gagal konek)
const char* AP_SSID = "ESP32_GAS_SENSOR";
const char* AP_PASSWORD = "12345678";  // Min 8 karakter

// Server endpoint untuk kirim data
// Server endpoint untuk kirim data
const char* SERVER_URL = "http://YOUR_SERVER_IP:PORT/server/oximonitor.php";

// RS485 Pins
#define RS485_RX_PIN  16  // GPIO16 = RX2
#define RS485_TX_PIN  17  // GPIO17 = TX2
#define RS485_DE_PIN  22  // GPIO22 = DE (Driver Enable)
#define RS485_RE_PIN  21  // GPIO21 = RE (Receiver Enable, active LOW)

// Modbus
#define SLAVE_ADDRESS   1
#define BAUD_RATE       9600

// Timing
// Timing
#define FLOW_SEND_INTERVAL_MS   100     // Kirim data flow setiap 100ms
#define VOLUME_SEND_INTERVAL_MS 300000  // Kirim data volume setiap 5 menit (300.000 ms)

// ==================== REGISTER SIARGO ====================
#define REG_FLOW_RATE    0x003A  // 58 - Flow rate (32-bit)
#define REG_TOTAL_LOW    0x003C  // 60-62 - Total accumulation (48-bit)
#define REG_CLEAR_TOTAL  0x00F2  // 242 - Clear total command
#define REG_WRITE_PROTECT 0x00FF // 255 - Unlock register

#define PASSWORD_UNLOCK  0xAA55
#define CMD_CLEAR_TOTAL  0x0001

// ==================== OBJECTS ====================
ModbusMaster node;
WebServer server(80);
HardwareSerial RS485Serial(2);  // Use UART2

// ==================== VARIABLES ====================
unsigned long lastFlowSendTime = 0;
unsigned long lastVolumeSendTime = 0;
float currentFlow = 0.0;
float currentTotal = 0.0;
bool sensorConnected = false;
bool isAPMode = false;  // Track WiFi mode

// ==================== RS485 CONTROL ====================
void preTransmission() {
  digitalWrite(RS485_RE_PIN, HIGH);  // Disable receive (RE active LOW)
  digitalWrite(RS485_DE_PIN, HIGH);  // Enable transmit
  delayMicroseconds(50);
}

void postTransmission() {
  delayMicroseconds(50);
  digitalWrite(RS485_DE_PIN, LOW);   // Disable transmit
  digitalWrite(RS485_RE_PIN, LOW);   // Enable receive (RE active LOW)
}

// ==================== MODBUS FUNCTIONS ====================
bool readFlowRate() {
  uint8_t result = node.readHoldingRegisters(REG_FLOW_RATE, 2);
  
  if (result == node.ku8MBSuccess) {
    uint32_t raw = ((uint32_t)node.getResponseBuffer(0) << 16) | node.getResponseBuffer(1);
    currentFlow = raw / 1000.0;  // Convert to L/min
    return true;
  }
  return false;
}

bool readTotalAccumulation() {
  uint8_t result = node.readHoldingRegisters(REG_TOTAL_LOW, 3);
  
  if (result == node.ku8MBSuccess) {
    uint16_t low = node.getResponseBuffer(0);
    uint16_t mid = node.getResponseBuffer(1);
    uint16_t high = node.getResponseBuffer(2);
    
    // Formula: low * 65536 + mid + high/1000
    currentTotal = (float)low * 65536.0 + (float)mid + (float)high / 1000.0;
    return true;
  }
  return false;
}

bool clearAccumulation() {
  Serial.println("[MODBUS] 🔓 Unlocking write protection...");
  
  // Unlock
  uint8_t result = node.writeSingleRegister(REG_WRITE_PROTECT, PASSWORD_UNLOCK);
  if (result != node.ku8MBSuccess) {
    Serial.println("[MODBUS] ❌ Failed to unlock");
    return false;
  }
  
  delay(100);
  
  // Clear total
  Serial.println("[MODBUS] 🗑️ Sending clear command...");
  result = node.writeSingleRegister(REG_CLEAR_TOTAL, CMD_CLEAR_TOTAL);
  
  if (result == node.ku8MBSuccess) {
    Serial.println("[MODBUS] ✅ Accumulation cleared!");
    return true;
  } else {
    Serial.printf("[MODBUS] ❌ Clear failed, error: 0x%02X\n", result);
    return false;
  }
}

// ==================== HTTP CLIENT ====================
// ==================== HTTP CLIENT ====================
void sendDataToServer(float flow, float volume, bool hasFlow, bool hasVolume) {
  // Skip sending if in AP mode (no internet)
  if (isAPMode) {
    Serial.println("[HTTP] ⏭️ Skipping (AP Mode - no server connection)");
    return;
  }
  
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] ❌ WiFi not connected, skipping send");
    return;
  }
  
  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<200> doc;
  doc["device_id"] = "ESP32_GAS_01";
  
  if (hasFlow) {
    doc["flow"] = flow;
  }
  
  if (hasVolume) {
    doc["volume"] = volume;
  }
  
  doc["timestamp"] = millis();
  
  String payload;
  serializeJson(doc, payload);
  
  Serial.printf("[HTTP] 📤 Sending: %s\n", payload.c_str());
  
  int httpCode = http.POST(payload);
  
  if (httpCode > 0) {
    Serial.printf("[HTTP] ✅ Response code: %d\n", httpCode);
    String response = http.getString();
    Serial.printf("[HTTP] 📥 Response: %s\n", response.c_str());
  } else {
    Serial.printf("[HTTP] ❌ Error: %s\n", http.errorToString(httpCode).c_str());
  }
  
  http.end();
}

// ==================== WEB UI HTML (PROGMEM) ====================
const char INDEX_HTML[] PROGMEM = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ESP32 Gas Sensor</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1a1a2e,#16213e);color:#fff;min-height:100vh;padding:20px}
    .container{max-width:500px;margin:0 auto}
    h1{text-align:center;margin-bottom:20px;font-size:1.5em}
    .card{background:rgba(255,255,255,0.1);border-radius:12px;padding:20px;margin:15px 0;backdrop-filter:blur(10px)}
    .card h3{color:#888;font-size:0.9em;margin-bottom:8px}
    .value{font-size:2.2em;font-weight:bold;color:#00ff88}
    .unit{font-size:0.5em;color:#888}
    .status{display:flex;align-items:center;gap:8px}
    .dot{width:12px;height:12px;border-radius:50%;animation:pulse 2s infinite}
    .dot.on{background:#00ff88}
    .dot.off{background:#ff4444}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
    .btn{width:100%;background:#e94560;color:#fff;border:none;padding:15px;border-radius:8px;font-size:1.1em;cursor:pointer;margin-top:10px}
    .btn:hover{background:#ff6b6b}
    .btn:disabled{background:#555;cursor:not-allowed}
    .settings{background:rgba(0,0,0,0.3);border-radius:8px;padding:15px;margin-top:20px}
    .settings label{display:block;margin-bottom:5px;color:#888;font-size:0.9em}
    .settings input{width:100%;padding:10px;border-radius:5px;border:none;background:#1a1a2e;color:#fff;font-size:1em}
    .footer{text-align:center;margin-top:20px;color:#555;font-size:0.8em}
    #lastUpdate{color:#888;font-size:0.8em;text-align:center}
  </style>
</head>
<body>
  <div class="container">
    <h1>🔥 ESP32 Gas Sensor</h1>
    
    <div class="card">
      <h3>FLOW RATE</h3>
      <div class="value"><span id="flow">--</span> <span class="unit">L/min</span></div>
    </div>
    
    <div class="card">
      <h3>TOTAL ACCUMULATION</h3>
      <div class="value"><span id="total">--</span> <span class="unit">m³</span></div>
    </div>
    
    <div class="card">
      <h3>SENSOR STATUS</h3>
      <div class="status">
        <div class="dot" id="statusDot"></div>
        <span id="statusText">--</span>
      </div>
    </div>
    
    <button class="btn" id="clearBtn" onclick="clearAcc()">🗑️ Clear Accumulation</button>
    
    <div class="settings">
      <label>Refresh Interval (ms)</label>
      <input type="number" id="refreshInput" min="500" max="60000" step="500" value="2000" onchange="saveSettings()">
    </div>
    
    <p id="lastUpdate">Last update: --</p>
    <p class="footer">Uptime: <span id="uptime">--</span></p>
  </div>
  
  <script>
    let refreshInterval = 2000;
    let timer = null;
    
    function loadSettings() {
      const saved = localStorage.getItem('refreshInterval');
      if (saved) {
        refreshInterval = parseInt(saved);
        document.getElementById('refreshInput').value = refreshInterval;
      }
    }
    
    function saveSettings() {
      refreshInterval = parseInt(document.getElementById('refreshInput').value);
      localStorage.setItem('refreshInterval', refreshInterval);
      startPolling();
    }
    
    function formatUptime(ms) {
      const s = Math.floor(ms/1000);
      const m = Math.floor(s/60);
      const h = Math.floor(m/60);
      return h + 'h ' + (m%60) + 'm ' + (s%60) + 's';
    }
    
    async function fetchData() {
      try {
        const res = await fetch('/data');
        const data = await res.json();
        
        document.getElementById('flow').textContent = data.flow_rate.toFixed(3);
        document.getElementById('total').textContent = (data.total_accumulation).toFixed(3);
        document.getElementById('uptime').textContent = formatUptime(data.uptime_ms);
        
        const dot = document.getElementById('statusDot');
        const txt = document.getElementById('statusText');
        if (data.sensor_connected) {
          dot.className = 'dot on';
          txt.textContent = 'Connected';
        } else {
          dot.className = 'dot off';
          txt.textContent = 'Disconnected';
        }
        
        document.getElementById('lastUpdate').textContent = 'Last update: ' + new Date().toLocaleTimeString();
      } catch (e) {
        document.getElementById('statusText').textContent = 'Error: ' + e.message;
      }
    }
    
    async function clearAcc() {
      const btn = document.getElementById('clearBtn');
      btn.disabled = true;
      btn.textContent = '⏳ Clearing...';
      
      try {
        const res = await fetch('/clear');
        const data = await res.json();
        btn.textContent = data.success ? '✅ Cleared!' : '❌ Failed';
        setTimeout(() => {
          btn.textContent = '🗑️ Clear Accumulation';
          btn.disabled = false;
          fetchData();
        }, 1500);
      } catch (e) {
        btn.textContent = '❌ Error';
        btn.disabled = false;
      }
    }
    
    function startPolling() {
      if (timer) clearInterval(timer);
      timer = setInterval(fetchData, refreshInterval);
    }
    
    loadSettings();
    fetchData();
    startPolling();
  </script>
</body>
</html>
)rawliteral";

// ==================== WEB SERVER HANDLERS ====================
void handleRoot() {
  server.send_P(200, "text/html", INDEX_HTML);
}

void handleClearAccumulation() {
  Serial.println("[WEB] 🌐 Clear accumulation requested");
  
  bool success = clearAccumulation();
  
  StaticJsonDocument<100> doc;
  doc["success"] = success;
  doc["message"] = success ? "Accumulation cleared" : "Failed to clear";
  
  String response;
  serializeJson(doc, response);
  
  server.send(success ? 200 : 500, "application/json", response);
}

void handleGetData() {
  StaticJsonDocument<200> doc;
  doc["flow_rate"] = currentFlow;
  doc["total_accumulation"] = currentTotal;
  doc["sensor_connected"] = sensorConnected;
  doc["uptime_ms"] = millis();
  
  String response;
  serializeJson(doc, response);
  
  server.send(200, "application/json", response);
}

void handleNotFound() {
  server.send(404, "text/plain", "Not Found");
}

// ==================== SETUP ====================
void setup() {
  Serial.begin(115200);
  Serial.println("\n");
  Serial.println("╔════════════════════════════════════════╗");
  Serial.println("║   ESP32 SIARGO Gas Sensor Monitor      ║");
  Serial.println("╚════════════════════════════════════════╝");
  
  // RS485 Setup
  Serial.println("[INIT] 🔌 Setting up RS485...");
  pinMode(RS485_DE_PIN, OUTPUT);
  pinMode(RS485_RE_PIN, OUTPUT);
  digitalWrite(RS485_DE_PIN, LOW);   // Start in receive mode
  digitalWrite(RS485_RE_PIN, LOW);   // Enable receiver
  
  RS485Serial.begin(BAUD_RATE, SERIAL_8N1, RS485_RX_PIN, RS485_TX_PIN);
  
  node.begin(SLAVE_ADDRESS, RS485Serial);
  node.preTransmission(preTransmission);
  node.postTransmission(postTransmission);
  Serial.println("[INIT] ✅ RS485 ready");
  
  // WiFi Setup - Try Station Mode first, fallback to AP
  Serial.printf("[WIFI] 📡 Connecting to %s", WIFI_SSID);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println(" Connected!");
    Serial.printf("[WIFI] ✅ Mode: STATION\n");
    Serial.printf("[WIFI] ✅ IP Address: %s\n", WiFi.localIP().toString().c_str());
    isAPMode = false;
  } else {
    Serial.println(" Failed!");
    Serial.println("[WIFI] ⚠️ Switching to AP Mode...");
    
    // Start Access Point
    WiFi.mode(WIFI_AP);
    WiFi.softAP(AP_SSID, AP_PASSWORD);
    
    IPAddress apIP = WiFi.softAPIP();
    Serial.printf("[WIFI] 📶 Mode: ACCESS POINT\n");
    Serial.printf("[WIFI] 📶 SSID: %s\n", AP_SSID);
    Serial.printf("[WIFI] 📶 Password: %s\n", AP_PASSWORD);
    Serial.printf("[WIFI] 📶 IP Address: %s\n", apIP.toString().c_str());
    isAPMode = true;
  }
  
  // Web Server Setup
  Serial.println("[WEB] 🌐 Starting web server...");
  server.on("/", handleRoot);
  server.on("/clear", handleClearAccumulation);
  server.on("/data", handleGetData);
  server.onNotFound(handleNotFound);
  server.begin();
  Serial.println("[WEB] ✅ Web server started");
  
  Serial.println("\n[READY] 🚀 System ready!");
  Serial.println("────────────────────────────────────────");
  if (isAPMode) {
    Serial.printf("Mode: AP (Connect to WiFi: %s)\n", AP_SSID);
    Serial.printf("Web UI: http://%s/\n", WiFi.softAPIP().toString().c_str());
    Serial.printf("API Data: http://%s/data\n", WiFi.softAPIP().toString().c_str());
    Serial.printf("Clear Acc: http://%s/clear\n", WiFi.softAPIP().toString().c_str());
  } else {
    Serial.println("Mode: STATION (Connected to WiFi)");
    Serial.printf("Web UI: http://%s/\n", WiFi.localIP().toString().c_str());
    Serial.printf("API Data: http://%s/data\n", WiFi.localIP().toString().c_str());
    Serial.printf("Clear Acc: http://%s/clear\n", WiFi.localIP().toString().c_str());
  }
  Serial.println("────────────────────────────────────────\n");
}

// ==================== LOOP ====================
void loop() {
  // Handle web server
  server.handleClient();
  
  unsigned long currentMillis = millis();

  // 1. Send Flow Rate every 100ms
  if (currentMillis - lastFlowSendTime >= FLOW_SEND_INTERVAL_MS) {
    lastFlowSendTime = currentMillis;
    
    if (readFlowRate()) {
      sensorConnected = true;
      sendDataToServer(currentFlow, 0, true, false);
    } else {
      sensorConnected = false;
      Serial.println("[SENSOR] ❌ Failed to read flow rate");
    }
  }

  // 2. Send Volume (Total Accumulation) every 5 minutes
  if (currentMillis - lastVolumeSendTime >= VOLUME_SEND_INTERVAL_MS) {
    lastVolumeSendTime = currentMillis;
    
    Serial.println("\n[SENSOR] 📊 Reading volume data (5 min interval)...");
    if (readTotalAccumulation()) {
      Serial.printf("[SENSOR] Total: %.3f L\n", currentTotal);
      sendDataToServer(0, currentTotal, false, true);
    } else {
      Serial.println("[SENSOR] ❌ Failed to read total");
    }
    Serial.println("────────────────────────────────────────");
  }
  
  delay(10);  // Small delay to prevent watchdog issues
}
