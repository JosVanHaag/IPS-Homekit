# IPS-Homekit

[![IP-Symcon 9.x](https://img.shields.io/badge/IP--Symcon-9.x-blue.svg)](https://www.symcon.de)
[![Run Tests](https://github.com/JosVanHaag/IPS-Homekit/workflows/Run%20Tests/badge.svg)](https://github.com/JosVanHaag/IPS-Homekit/actions)
[![Check Style](https://github.com/JosVanHaag/IPS-Homekit/workflows/Check%20Style/badge.svg)](https://github.com/JosVanHaag/IPS-Homekit/actions)

Nativer Apple HomeKit-Stack für IP-Symcon. Das Modul implementiert das HomeKit Accessory Protocol (HAP) direkt in PHP — ohne externen Bridge-Prozess, ohne Polling. Statusänderungen werden per MessageSink an HomeKit gepusht.

_Dieses Repository enthält keine von Apple zertifizierte Bridge und wird nicht von der Symcon GmbH angeboten._

---

## Voraussetzungen

- IP-Symcon 9.0 oder neuer
- Netzwerk mit mDNS/Bonjour (Homekit-Gerät und IPS-Server im selben Subnetz)
- Bei Docker-Betrieb: Container im **Host-Modus** (`network_mode: host`)

---

## Installation

Im [Module Control](https://www.symcon.de/service/dokumentation/modulreferenz/module-control/) folgende URL eintragen:

```
https://github.com/JosVanHaag/IPS-Homekit
```

---

## Einrichtung

Nach der Installation eine Instanz vom Typ **HomeKit Bridge** anlegen. In der Konfiguration werden die gewünschten Geräte über die jeweiligen Panels hinzugefügt. Jeder Eintrag benötigt einen Namen (so wird das Gerät in Siri und der Home-App angesprochen) sowie die zugehörigen IPS-Variablen-IDs.

Sobald alle Geräte eingetragen und mit **Änderungen übernehmen** bestätigt sind, erscheint in der Instanz ein Kopplungs-QR-Code bzw. ein PIN. Diesen in der Home-App unter **Zubehör hinzufügen** eingeben — die Bridge wird dort als **Symcon** gefunden.

Nach der Kopplung zeigt die Spalte **Status** für jedes Gerät **OK** an, sofern alle Variablen korrekt konfiguriert sind.

---

## Unterstützte Gerätetypen

| Gerätetyp | Beschreibung |
|-----------|-------------|
| [Lampe (Schaltbar)][lampe-schaltbar] | Ein/Aus über Bool-Variable |
| [Lampe (Dimmbar)][lampe-dimmbar] | Ein/Aus + Helligkeit |
| [Lampe (Farbig)][lampe-farbig] | Ein/Aus + Helligkeit + Farbe (HSB) |
| [Lampe (Experte)][lampe-experte] | Separate State- und Brightness-Variablen |
| [Lampe (Farbtemperatur)][lampe-farbtemperatur] | Experte + Farbtemperatur in Mired |
| [Türklingel][tuerklingel] | Bool-Variable, löst HomeKit-Push-Notification aus |
| [Bewegungsmelder][bewegungsmelder] | Bool-Variable, Bewegung erkannt |
| [Präsenzmelder][praesenzmelder] | Bool-Variable, Präsenz erkannt (uint8-Characteristic) |
| [Fenster (Position)][fenster-position] | Position 0–100 % |
| [Fenster (Hoch/Runter)][fenster-hoch-runter] | Binäre Richtungssteuerung |
| [Feuchtigkeitssensor][feuchtigkeitssensor] | Float-Variable, relative Luftfeuchte |
| [Lüfter][luefter] | Ein/Aus + Drehzahl |
| [Garagentor][garagentor] | Integer-Variable (0=offen, 4=geschlossen) |
| [Helligkeitssensor][helligkeitssensor] | Float-Variable, Lux |
| [Kontaktsensor][kontaktsensor] | Bool-Variable, offen/geschlossen |
| [Kohlendioxid Sensor][kohlendioxid-sensor] | CO₂-Konzentration |
| [Kohlenmonoxid Sensor][kohlenmonoxid-sensor] | CO-Konzentration |
| [Lautsprecher][lautsprecher] | Lautstärke + Stummschaltung |
| [Leckagesensor][leckagesensor] | Bool-Variable, Leckage erkannt |
| [Luftgütesensor][luftguetesensor] | Luftqualitätsstufe |
| [Rauchmelder][rauchmelder] | Bool-Variable, Rauch erkannt |
| [Rollladen/Jalousie (Position)][rollladen-jalousie-position] | Position 0–100 % |
| [Rollladen/Jalousie (Hoch/Runter)][rollladen-jalousie-hoch-runter] | Binäre Richtungssteuerung |
| [Schloss][schloss] | Bool-Variable, gesperrt/entsperrt |
| [Temperatursensor][temperatursensor] | Float-Variable, Celsius |
| [Thermostat][thermostat] | Ist- + Soll-Temperatur, alle Betriebsmodi |
| [Thermostat (Nur Heizen)][thermostat-nur-heizen] | Ist- + Soll-Temperatur, nur Off/Heat |
| [Thermostat (Nur Heizen + Batterie)][thermostat-nur-heizen-+-batterie] | Wie oben, zusätzlich LOWBAT-Anzeige |
| [Zwischenstecker][zwischenstecker] | Ein/Aus mit Verbrauchsanzeige |
| [Sicherheitssystem][sicherheitssystem] | Scharf/Unscharf mit Alarmmodus |
| [Zustandsloser programmierbarer Schalter][zustandsloser-programmierbarer-schalter] | Szenen-Auslöser |
| [Expertenoptionen][expertenoptionen] | Erweiterte Konfiguration für alle Typen |

---

## Architektur

Eine Übersicht über den internen Aufbau des HAP-Stacks (Dispatch-Mechanismus, Service-/Characteristic-Hierarchie, Pairing, mDNS) findet sich in [docs/specs.md](docs/specs.md).

---

[lampe-schaltbar]: docs/types/lampe-schaltbar.md
[lampe-dimmbar]: docs/types/lampe-dimmbar.md
[lampe-farbig]: docs/types/lampe-farbig.md
[lampe-experte]: docs/types/lampe-experte.md
[lampe-farbtemperatur]: docs/types/lampe-farbtemperatur.md
[tuerklingel]: docs/types/tuerklingel.md
[bewegungsmelder]: docs/types/bewegungsmelder.md
[praesenzmelder]: docs/types/praesenzmelder.md
[fenster-position]: docs/types/fenster-position.md
[fenster-hoch-runter]: docs/types/fenster-hoch-runter.md
[feuchtigkeitssensor]: docs/types/feuchtigkeitssensor.md
[luefter]: docs/types/luefter.md
[garagentor]: docs/types/garagentor.md
[helligkeitssensor]: docs/types/helligkeitssensor.md
[kontaktsensor]: docs/types/kontaktsensor.md
[kohlendioxid-sensor]: docs/types/kohlendioxid-sensor.md
[kohlenmonoxid-sensor]: docs/types/kohlenmonoxid-sensor.md
[lautsprecher]: docs/types/lautsprecher.md
[leckagesensor]: docs/types/leckagesensor.md
[luftguetesensor]: docs/types/luftguetesensor.md
[rauchmelder]: docs/types/rauchmelder.md
[rollladen-jalousie-position]: docs/types/rollladen-jalousie-position.md
[rollladen-jalousie-hoch-runter]: docs/types/rollladen-jalousie-hoch-runter.md
[schloss]: docs/types/schloss.md
[temperatursensor]: docs/types/temperatursensor.md
[thermostat]: docs/types/thermostat.md
[thermostat-nur-heizen]: docs/types/thermostat-nur-heizen.md
[thermostat-nur-heizen-+-batterie]: docs/types/thermostat-nur-heizen-+-batterie.md
[zwischenstecker]: docs/types/zwischenstecker.md
[expertenoptionen]: docs/types/expertenoptionen.md
[sicherheitssystem]: docs/types/sicherheitssystem.md
[zustandsloser-programmierbarer-schalter]: docs/types/zustandsloser-programmierbarer-schalter.md
