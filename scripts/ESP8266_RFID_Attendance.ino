/*
 * =====================================================================================
 * Smart School RFID Attendance System ESP8266 Sketch
 * Hardware Required:
 * - ESP8266 (NodeMCU) Board
 * - MFRC522 RFID Reader
 * - Active Buzzer & LED for audio/visual feedback (optional)
 *
 * MFRC522 NodeMCU Wiring Reference:
 * ---------------------------------------------------
 * MFRC522 Pin  | NodeMCU Pin | Pin Label
 * -------------|-------------|-----------------------
 * SDA (SS)     | D2          | GPIO4 (configurable SS)
 * SCK          | D5          | GPIO14 (SPI SCK)
 * MOSI         | D7          | GPIO13 (SPI MOSI)
 * MISO         | D6          | GPIO12 (SPI MISO)
 * IRQ          | None        | Not connected
 * GND          | GND         | GND
 * RST          | D1          | GPIO5 (configurable RST)
 * 3.3V         | 3.3V        | 3.3V (Do not connect to 5V!)
 *
 * Feedback Components:
 * - BUZZER Pin | D8          | GPIO15
 * - GREEN LED  | D4          | GPIO2 (Onboard LED on most boards, active LOW)
 * - RED LED    | D3          | GPIO0
 * =====================================================================================
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <WiFiClient.h>

// --------------------------------------------------
// CONFIGURATION: Customize these for your environment
// --------------------------------------------------
const char* ssid = "Airtel_Koushik";          // Your WiFi Network SSID
const char* password = "1011110@Me";  // Your WiFi Network Password

// Your Smart School Domain URL (No trailing slash)
// E.g., "http://yourdomain.com" or local IP "http://192.168.1.100/smart_school_src"
const char* serverUrl = "http://192.168.1.11/smart_school_src/Rfid/scan";

// Secret token configured in the Admin Panel -> RFID Devices
const char* deviceToken = "YOUR_SECURE_DEVICE_TOKEN"; 

// Hardware Pins Configuration
#define SS_PIN D2
#define RST_PIN D1
#define BUZZER_PIN D8
#define GREEN_LED_PIN D4
#define RED_LED_PIN D3

MFRC522 mfrc522(SS_PIN, RST_PIN); // MFRC522 Instance

void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println("\n\n========================================");
  Serial.println("Smart School RFID Attendance System Starting...");
  Serial.println("========================================");

  // Initialize Pin Modes
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(GREEN_LED_PIN, OUTPUT);
  pinMode(RED_LED_PIN, OUTPUT);

  // Set default output state (LEDs off, Buzzer off)
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(GREEN_LED_PIN, HIGH); // Active LOW on NodeMCU
  digitalWrite(RED_LED_PIN, LOW);

  // Initialize SPI & RFID Card Reader
  SPI.begin();
  mfrc522.PCD_Init();
  Serial.println("MFRC522 Card Reader Initialized successfully.");

  // Connect to WiFi network
  connectToWiFi();
}

void loop() {
  // Enforce WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    connectToWiFi();
    return;
  }

  // Look for new RFID cards
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  // Select one of the RFID cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // Read card UID and convert to a continuous hex string
  String cardUid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    cardUid += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    cardUid += String(mfrc522.uid.uidByte[i], HEX);
  }
  cardUid.toUpperCase();

  Serial.println("\n----------------------------------------");
  Serial.print("RFID Card Scanned! UID: ");
  Serial.println(cardUid);

  // Send the scan request to the server
  sendScanData(cardUid);

  // Halt card to prevent duplicate readings in a single scan action
  mfrc522.PICC_HaltA();
  delay(1500); // 1.5 seconds cooldown between scans
}

void connectToWiFi() {
  Serial.print("Connecting to WiFi Network: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
    
    // Quick flash red LED during connection attempts
    digitalWrite(RED_LED_PIN, !digitalRead(RED_LED_PIN));
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    digitalWrite(RED_LED_PIN, LOW); // Off
    Serial.println("\nWiFi Connected successfully!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Device MAC Address (UID): ");
    Serial.println(WiFi.macAddress());
    
    // Success audio tone (2 quick beeps)
    beep(100, 2);
  } else {
    Serial.println("\nWiFi connection failed! Retrying in loop...");
    digitalWrite(RED_LED_PIN, HIGH); // Steady RED for WiFi error
  }
}

void sendScanData(String cardUid) {
  WiFiClient client;
  HTTPClient http;

  Serial.print("Sending card to API: ");
  Serial.println(serverUrl);

  http.begin(client, serverUrl);
  http.addHeader("Content-Type", "application/json");

  // Construct request JSON payload
  // Uses physical MAC Address as device_uid dynamically
  String deviceMac = WiFi.macAddress();
  String jsonPayload = "{\"device_uid\":\"" + deviceMac + 
                       "\",\"device_token\":\"" + String(deviceToken) + 
                       "\",\"rfid_uid\":\"" + cardUid + "\"}";

  Serial.print("Request Payload: ");
  Serial.println(jsonPayload);

  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.print("HTTP Status Code: ");
    Serial.println(httpCode);
    Serial.print("Server Response: ");
    Serial.println(response);

    if (httpCode == 200) {
      handleServerResponse(response);
    } else {
      triggerErrorFeedback();
    }
  } else {
    Serial.print("Connection Error: ");
    Serial.println(http.errorToString(httpCode).c_str());
    triggerErrorFeedback();
  }

  http.end();
}

void handleServerResponse(String jsonResponse) {
  // Simple JSON parsing to determine outcome without external heavy library dependencies
  if (jsonResponse.indexOf("\"status\":\"check_in\"") > 0) {
    Serial.println("Scan Success: Staff Checked In!");
    
    // Green feedback, single long beep
    digitalWrite(GREEN_LED_PIN, LOW); // On (active low)
    digitalWrite(BUZZER_PIN, HIGH);
    delay(500);
    digitalWrite(GREEN_LED_PIN, HIGH); // Off
    digitalWrite(BUZZER_PIN, LOW);
  } 
  else if (jsonResponse.indexOf("\"status\":\"check_out\"") > 0) {
    Serial.println("Scan Success: Staff Checked Out!");
    
    // Green feedback, 2 distinct beeps
    digitalWrite(GREEN_LED_PIN, LOW); // On (active low)
    beep(150, 2);
    digitalWrite(GREEN_LED_PIN, HIGH); // Off
  } 
  else if (jsonResponse.indexOf("\"status\":\"duplicate\"") > 0) {
    Serial.println("Scan Ignored: Attendance already fully marked today!");
    
    // Amber/Yellow feedback: 3 rapid warning beeps
    beep(80, 3);
  } 
  else {
    // Other error statuses (invalid card, unauthorized device etc.)
    Serial.println("Scan Rejected: Access Denied!");
    triggerErrorFeedback();
  }
}

void triggerErrorFeedback() {
  // Steady red light and 1 continuous long alarm beep
  digitalWrite(RED_LED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(800);
  digitalWrite(RED_LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

void beep(int duration, int count) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) {
      delay(duration); // Interval
    }
  }
}
