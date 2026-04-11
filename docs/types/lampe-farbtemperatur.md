### Beschreibung

Geräte vom Typ Lampe (Farbtemperatur) beschreiben Lampen mit separaten Variablen für Schaltzustand, Helligkeit und Farbtemperatur. Geeignet für Zigbee2MQTT-Lampen mit Kelvin/Mired-Farbtemperatursteuerung.

### Parameter

Name                  | Beschreibung
--------------------- | ---------------
Name                  | Name mit dem das Gerät über HomeKit angesprochen werden kann
Status Variable       | Eine schaltbare Variable vom Typ Boolean, über welche das Licht ein- oder ausgeschaltet wird
Helligkeits Variable  | Eine schaltbare Variable vom Typ Integer oder Float, über welche das Licht gedimmt wird
Farbtemperatur Variable | Eine schaltbare Variable vom Typ Integer mit dem Farbtemperaturwert in Mired (50–400)

#### Mögliche Aktionen

Aktion                   | Beschreibung                                            | Möglicher Satz zum Aktivieren
------------------------ | ------------------------------------------------------- | -----------------------------
An oder Aus schalten     | Schaltet die Status-Variable auf den eingestellten Wert | "Hey Siri, schalte _<Name\>_ an."
Dimmen                   | Schaltet die Helligkeits-Variable auf den angegebenen Wert | "Hey Siri, dimme _<Name\>_ auf 40%."
Farbtemperatur einstellen | Schaltet die Farbtemperatur-Variable auf den angegebenen Mired-Wert | "Hey Siri, stelle _<Name\>_ wärmer."
