### Beschreibung

Geräte vom Typ Türklingel lösen in der Home-App und auf allen verknüpften Apple-Geräten (iPhone, iPad, Apple Watch) eine Push-Benachrichtigung aus, sobald die zugeordnete Bool-Variable auf `true` wechselt. Geeignet für beliebige Klingelkontakte, die in IPS als Bool-Variable abgebildet sind (z. B. HomeMatic-Eingänge, KNX-Taster).

### Parameter

Name          | Beschreibung
------------- | ---------------
Name          | Name, unter dem die Klingel in HomeKit angesprochen wird
VariablenID   | Eine Variable vom Typ Boolean, die den Klingel-Impuls signalisiert (true = gedrückt)

#### Mögliche Aktionen

Aktion                | Beschreibung                                                        | Beispiel
--------------------- | ------------------------------------------------------------------- | --------
Klingel-Benachrichtigung | Löst beim Wechsel auf true eine Push-Notification in HomeKit aus | Klingel an der Haustür drücken
