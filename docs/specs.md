# IPS-Homekit — Architektur

## Überblick

IPS-Homekit implementiert das [HomeKit Accessory Protocol (HAP)](https://developer.apple.com/homekit/) nativ in PHP direkt in IP-Symcon. Es gibt keinen externen Bridge-Prozess — der HAP-Stack läuft als IPS-Modul und kommuniziert über TCP mit der Home-App.

```
Home-App  <──HAP/TCP──>  IPS HomeKitBridge  <──MessageSink──>  IPS-Variablen
```

Statusänderungen fließen per Push (MessageSink) vom IPS-Objektmodell in Richtung HomeKit. Es gibt kein Polling.

---

## Komponentenhierarchie

```
HAPAccessory          (ein Gerät, z. B. "Wohnzimmerlampe")
  └── HAPService[]    (eine Funktion, z. B. Lightbulb, BatteryService)
        └── HAPCharacteristic[]  (ein Attribut, z. B. On, Brightness, BatteryLevel)
```

- Jedes **Accessory** enthält mindestens zwei Services: `AccessoryInformation` und den eigentlichen Geräte-Service.
- Jeder **Service** ist durch eine HAP-UUID definiert (z. B. `0x43` für Lightbulb, `0x4A` für Thermostat).
- Jede **Characteristic** hat ein Format (bool, uint8, float, …), Permissions (PairedRead / PairedWrite / Notify) und optionale Constraints (minValue, maxValue, minStep).

---

## Instance-ID-Schema (iid)

Jedes Element im Accessory-Baum bekommt eine ganzzahlige iid, über die die Home-App es adressiert:

| Bereich | Bedeutung |
|---------|-----------|
| 1 | Accessory-Root |
| 1–99 | AccessoryInformation-Service + seine Characteristics |
| 100, 200, 300, … | Beginn je eines weiteren Service |
| 101–199, 201–299, … | Characteristics des jeweiligen Service |

Die iid ergibt sich aus `serviceIndex * 100 + characteristicIndex`. Characteristic-Zugriffe werden intern über `instanceID % 100` auf den richtigen Service gemappt.

---

## Dispatch-Mechanismus

Der Kern des Stacks leitet Methodennamen direkt vom PHP-Klassennamen der Characteristic ab:

```
read  + substr(get_class($characteristic), 3)
write + substr(get_class($characteristic), 3)
notify+ substr(get_class($characteristic), 3)
```

Das `HAP`-Präfix (3 Zeichen) wird abgeschnitten. Aus `HAPCharacteristicBrightness` werden also:

```php
readCharacteristicBrightness()
writeCharacteristicBrightness($value)
notifyCharacteristicBrightness()   // gibt IPS-Variablen-IDs zurück
```

Diese Methoden müssen im jeweiligen Accessory implementiert sein. Für optionale Characteristics (z. B. ColorTemperature im Lightbulb-Service) prüft der Stack per `method_exists()` ob die Methode vorhanden ist — fehlt sie, wird die Characteristic weggelassen.

---

## Neue Accessory-Typen erstellen

Jeder Typ besteht aus drei Schichten:

1. **Characteristic** (`characteristics/<name>.php`) — nur nötig wenn eine bestehende HAP-Characteristic angepasst wird (z. B. `maxValue` einschränken)
2. **Service** (`services/<name>.php`) — definiert welche Characteristics der Service enthält
3. **Accessory** (`accessories/<name>.php`) — implementiert `read*`, `write*`, `notify*` und die Konfigurationsklasse

Der Klassenname der Accessory-Konfiguration muss `HAPAccessoryConfiguration<TypeName>` heißen. Der Typ wird über `HomeKitManager::registerAccessory('<TypeName>')` am Ende der Datei registriert.

Für Accessories mit mehreren Services (z. B. Thermostat + Battery) erbt die Klasse direkt von `HAPAccessoryBase` und übergibt alle Services im Konstruktor.

---

## Authentifizierung und Pairing

Das Pairing läuft über **SRP6a** (Secure Remote Password, 3072-Bit-Gruppe nach RFC 5054 / Apple HAP 4.6.2). Die Implementierung liegt in `HomeKitBridge/srp.php`.

Nach erfolgreichem Pairing werden Session-Keys per **HKDF-SHA512** abgeleitet. Die Kommunikation ist mit **ChaCha20-Poly1305** verschlüsselt.

Pairing-Daten (Geräte-Credentials) werden persistent in der IPS-Instanz gespeichert.

---

## Geräteerkennung (mDNS/Bonjour)

Die Bridge meldet sich per mDNS als `_hap._tcp`-Dienst an. Dabei werden folgende TXT-Records gesetzt:

| Key | Bedeutung |
|-----|-----------|
| `c#` | Konfigurationsnummer — wird bei Geräteänderungen inkrementiert |
| `s#` | State-Nummer |
| `ff` | Feature-Flags |
| `id` | Bridge-MAC (eindeutiger Identifier) |
| `md` | Modellname |
| `pv` | HAP-Protokollversion |
| `sf` | Status-Flags (1 = noch nicht gekoppelt) |

---

## Protokoll-Encoding

Für strukturierte Daten (Pairing-Nachrichten, Kamera-Streams) verwendet HAP **TLV8** (Type-Length-Value mit 1-Byte-Tag und 1-Byte-Length). Die Implementierung liegt in `HomeKitBridge/hap.php`.

Für einfache Characteristic-Werte wird JSON über HTTP/1.1 verwendet.
