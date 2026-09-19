### Beschreibung

Geräte vom Typ Türklingel lösen in der Home-App und auf allen verknüpften Apple-Geräten (iPhone, iPad, Apple Watch) eine Push-Benachrichtigung aus, sobald die zugeordnete Bool-Variable auf `true` wechselt. Geeignet für beliebige Klingelkontakte, die in IPS als Bool-Variable abgebildet sind (z. B. HomeMatic-Eingänge, KNX-Taster).

> **Hinweis: Apple Home zeigt "Nicht unterstützt"**
>
> Der HAP-Türklingel-Dienst (Service 0x121) wird von Apples Home-App nur vollständig dargestellt,
> wenn er Teil eines Video-Türklingel-Zubehörs (HAP-Kategorie 18 mit Kamera-Diensten) ist.
> In einer Bridge (Kategorie 2, ohne Kamera) erscheint das Tile als "Nicht unterstützt".
>
> **Die Push-Benachrichtigungen funktionieren trotzdem korrekt** — ein Wechsel der Variable auf
> `true` löst zuverlässig eine Klingel-Notification auf iPhone, iPad und Apple Watch aus.
>
> Drittanbieter-Apps (z. B. Eve, Home+) stellen das Tile korrekt dar.
> Dies ist eine bekannte Einschränkung von Apples Home-App (bestätigt durch den homebridge-Maintainer
> ebaauw, homebridge/HAP-NodeJS#3313).

### Parameter

Name          | Beschreibung
------------- | ---------------
Name          | Name, unter dem die Klingel in HomeKit angesprochen wird
VariablenID   | Eine Variable vom Typ Boolean, die den Klingel-Impuls signalisiert (true = gedrückt)

#### Mögliche Aktionen

Aktion                | Beschreibung                                                        | Beispiel
--------------------- | ------------------------------------------------------------------- | --------
Klingel-Benachrichtigung | Löst beim Wechsel auf true eine Push-Notification in HomeKit aus | Klingel an der Haustür drücken
