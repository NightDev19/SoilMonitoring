#include <ESP8266WiFi.h>
#include <BlynkSimpleEsp8266.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <SoftwareSerial.h>
#include <ESP8266HTTPClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include "Arduino.h"
#include <EMailSender.h>
#include "TimeAndDate.h"

#define RE D1
#define DE D2
#define ONE_WIRE_BUS D6
#define flowSensorPin D5
OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

const uint32_t TIMEOUT = 500UL;
const byte moist[] = {0x01, 0x03, 0x00, 0x00, 0x00, 0x01, 0x84, 0x0A};
const byte temp[] = {0x01, 0x03, 0x00, 0x01, 0x00, 0x01, 0xD5, 0xCA};
const byte EC[] = {0x01, 0x03, 0x00, 0x02, 0x00, 0x01, 0x25, 0xCA};

const float calibrationFactor = 120;
volatile int pulseCount = 0;
float flowRate = 0.0;
unsigned long prevMillis = 0;

char auth[] =  "2b3-H-7I-FVl5Uza43JKgUalNsFkALuo"; // blynk auth
const char *ssid = "PLDTHOMEFIBR14281";
const char *pass = "Erovoutika@123";
const char* server = "http://192.168.100.227/Soil_Monitoring/post-sensor-data.php";  
const char* server2 = "https://soilqualitymonitoring.000webhostapp.com/post-sensor-data.php";           
const char* fingerprint = "1276cc68cd52ef8d36031e2a23aec7a58af1dcdfecb5030822414ed763fcd809";

EMailSender emailSend("example@gmail.com", "App Gmail Password"); // email and password

byte values[11];
SoftwareSerial mod(D7, D8); // Rx pin, Tx pin

float tempValue; 
int moistureValue; 
int val3; 

String postData;

void setup() {

  pinMode(D3, OUTPUT);
  pinMode(flowSensorPin, INPUT_PULLUP);
  Serial.begin(9600);
  mod.begin(4800);
  pinMode(RE, OUTPUT);
  pinMode(DE, OUTPUT);
  sensors.begin(); 

  WiFi.begin(ssid, pass);
    while (WiFi.status() != WL_CONNECTED) {
    Serial.println("Connecting...");
    delay(1000);
  }
  Serial.print("Connected to WiFi.");
  Blynk.begin(auth, ssid, pass);
  Blynk.virtualWrite(V6, "Soil Sensor Online");
  timeClient.begin();
  timeClient.setTimeOffset(28800);
}

void notify(){
    EMailSender::EMailMessage message;
    message.subject = "Soil Health Warning!";
    message.message = "Moisture is low!<br>WaterPump was activated!<br>website url here";

    EMailSender::Response resp = emailSend.send("sherwintajan143@gmail.com", message);

   /* if (resp.status == 1) {
        Serial.println("Email sent successfully");
    } else {
        Serial.println("Email failed to send");
    }  */
}

void notify1(){
    EMailSender::EMailMessage message;
    message.subject = "Soil Health Warning";
    message.message = "Moisture is low!<br>But automatic watering is off!<br>website url here";

    EMailSender::Response resp = emailSend.send("sherwintajan143@gmail.com", message);

   /* if (resp.status == 1) {
        Serial.println("Email sent successfully");
    } else {
        Serial.println("Email failed to send");
    } */

}

void sendDataToServer(float temperature, int moisture, int conductivity, float flow) {
  
     if (WiFi.status() == WL_CONNECTED) {
        HTTPClient http;
        WiFiClientSecure client; 
        client.setFingerprint(fingerprint); 
    String postData = "temperature=" + String(temperature) + "&moisture=" + String(moisture) + "&conductivity=" + String(conductivity) + "&flow=" + String(flow);

    if (http.begin(client,server2)) {
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");

            int httpCode = http.POST(postData);

            if (httpCode == 200) {
                //Serial.println("Data sent successfully.");
            } else {
                //Serial.println("Failed to send data.");
            }

            http.end();
        } else {
            //Serial.println("Connection to server failed.");
        }
    } else {
        //Serial.println("WiFi Disconnected");
    }
} 



// void sendDataToServer(float temperature, int moisture, int conductivity, float flow) {
//    if(WiFi.status() == WL_CONNECTED){
//     HTTPClient http; 
//     WiFiClient client; 
   
//     postData = "temperature=" + String(temperature) + "&moisture=" + String(moisture) + "&conductivity=" + String(conductivity) + "&flow=" + String(flow);
    
//     http.begin(client, server);
    
//     http.addHeader("Content-Type", "application/x-www-form-urlencoded");

//     //Serial.print("postData: "); Serial.println(postData);

//     int httpCode = http.POST(postData); 
    
//     if (httpCode == 200) { 
//       Serial.println("Values updated successfully."); 
//      // String webpage = http.getString();   
//      // Serial.println(webpage + "\n"); 
//     }
//     else { 
//       Serial.println("Failed to send data." /*+ String(httpCode)*/);
//       http.end(); 
//       return; 
//     } 

//     http.end();
//   }
//   else{
//     Serial.println("WiFi Disconnected");
//   }

// }

void loop() {

  uint16_t val3;
  uint16_t val1;
  sensors.requestTemperatures();
  tempValue = sensors.getTempCByIndex(0);
  val3 = conductivity();
  Blynk.run();
  Blynk.virtualWrite(V1, tempValue);
  Blynk.virtualWrite(V2, moistureValue);
  Blynk.virtualWrite(V3, val3);
  //Blynk.virtualWrite(V4, tempValue);
  //Blynk.virtualWrite(V4, moistureValue);
  //Blynk.virtualWrite(V5, val3);
  Blynk.virtualWrite(V4, flowRate);


  static unsigned long databaseMillis = 0;
  const unsigned long databaseInterval = 59000; 
  unsigned long currentMillis = millis();
  read_flowSensor(); 

  timeClient.update();
  int currentHour = timeClient.getHours();
  int currentMinute = timeClient.getMinutes();
  time_t now = timeClient.getEpochTime();
  struct tm *ptm = gmtime(&now);
  int currentDay = ptm->tm_mday;
  int currentMonth = ptm->tm_mon + 1;  
  int currentYear = ptm->tm_year + 1900;

  //Serial.printf("Date & Time: %s %02d/%02d/%d - %02d:%02d\n", weekDays[ptm->tm_wday], currentMonth, currentDay, currentYear, currentHour, currentMinute);

  if (weekDays[ptm->tm_wday] == "Saturday"||weekDays[ptm->tm_wday] == "Monday"||weekDays[ptm->tm_wday] == "Wednesday"){  
  Serial.println("Automatic Feature Active!");
      if (moistureValue<=40){digitalWrite(D3, HIGH); Serial.println("Water Pump Active!"); notify();}
      else                  {digitalWrite(D3,LOW);   Serial.println("Water Pump Off!");}
  }
  else {Serial.println("Automatic Feature Inactive!"); 
        if (moistureValue<=40){digitalWrite(D3, LOW); notify1();}}

    getData();
    Serial.println("**************************************************");



  if (currentMillis - databaseMillis >= databaseInterval) {

    tempValue = sensors.getTempCByIndex(0);

    val1 = moisture();
    if (val1 > 30000) {
      val1 = 30000;
    }
    moistureValue = map(val1, 184, 30000, 5, 100);
    val3 = conductivity();
    
    sendDataToServer(tempValue, moistureValue, val3, flowRate);  // for localhost
    //sendDataToServer2(tempValue, moistureValue, val3, flowRate); // for global
    databaseMillis = currentMillis;
  }
}



void getData(){
    
    uint16_t val3;
    uint16_t val1;
    sensors.requestTemperatures();
    tempValue = sensors.getTempCByIndex(0);
    val3 = conductivity();
    val1 = moisture();

    if (val1 > 30000) {
      val1 = 30000;
    }
    moistureValue = map(val1, 184, 30000, 0, 100);

    /*Serial.print("Moisture = ");
    Serial.println(moistureValue);

    Serial.print("Temperature(C) = ");
    Serial.println(tempValue);

    Serial.print("Cndctvty(uS/cm) = "); 
    Serial.println(val3);

    Serial.print("Flow(Ltr/min) = ");
    Serial.println(flowRate);   */
}


int16_t moisture() {
  uint32_t startTime = 0;
  uint8_t byteCount = 0;

  digitalWrite(DE, HIGH);
  digitalWrite(RE, HIGH);
  delay(10);
  mod.write(moist, sizeof(moist));
  mod.flush();
  digitalWrite(DE, LOW);
  digitalWrite(RE, LOW);

  startTime = millis();
  while (millis() - startTime <= TIMEOUT) {
    if (mod.available() && byteCount < sizeof(values)) {
      values[byteCount++] = mod.read();
      printHexByte(values[byteCount - 1]); //myghad
    }
  }
  //Serial.println();
  return (int16_t)(values[4] << 8 | values[5]);
}

int16_t conductivity() {
  uint32_t startTime = 0;
  uint8_t byteCount = 0;

  digitalWrite(DE, HIGH);
  digitalWrite(RE, HIGH);
  delay(10);
  mod.write(EC, sizeof(EC));
  mod.flush();
  digitalWrite(DE, LOW);
  digitalWrite(RE, LOW);

  startTime = millis();
  while (millis() - startTime <= TIMEOUT) {
    if (mod.available() && byteCount < sizeof(values)) {
      values[byteCount++] = mod.read();
      printHexByte(values[byteCount - 1]);
    }
  }
  //Serial.println();
  return (int16_t)(values[4] << 8 | values[5]);
}

void printHexByte(byte b) {
  //Serial.print((b >> 4) & 0xF, HEX);
 // Serial.print(b & 0xF, HEX);
 // Serial.print(' ');
}


void read_flowSensor() {

  unsigned long currentMillis = millis();
  if (currentMillis - prevMillis >= 1000) {
    detachInterrupt(digitalPinToInterrupt(flowSensorPin));  
    flowRate = (pulseCount / calibrationFactor) * 18.2;  
    pulseCount = 0;
    attachInterrupt(digitalPinToInterrupt(flowSensorPin), flowSensorISR, FALLING); 
    prevMillis = currentMillis;
}
}

ICACHE_RAM_ATTR void flowSensorISR() {
  pulseCount++;
}



/*
CREATE TABLE `soil` (                                             // SQL Code to create the table
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `temperature` DECIMAL(5, 2),
    `moisture` INT,
    `conductivity` INT,
    `flow` DECIMAL(5, 2)
);   */


