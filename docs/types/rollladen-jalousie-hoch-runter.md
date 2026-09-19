### Beschreibung

Geräte vom Typ Rollladen/Jalousie (Hoch/Runter) beschreiben Geräte, die einen/eine Rollladen/Jalousie öffen bzw. schließen können.

### Parameter

Name       | Beschreibung
---------- | ---------------
Name       | Name mit dem das Gerät über HomeKit angesprochen werden kann
Variable   | Eine schaltbare Variable vom Typ Integer, über welche Rollladen/Jalousien geöffnet bzw. geschlossen wird. Die Variable muss das Profil ~ShutterMoveStop oder ~ShutterMoveStep gesetzt haben und die Aktion dies korrekt unterstützen.
Rückmeldung | Optional. Eine Variable vom Typ Integer oder Float, aus der die tatsächliche Stellung gelesen wird. Ohne Rückmeldung wird die Stellung aus dem letzten Fahrbefehl abgeleitet.

### Rückmeldung

Ohne Rückmeldung zeigt HomeKit den zuletzt über die Variable gesendeten Befehl an. Wird der Rollladen auf anderem Weg gefahren (Wandtaster, Gruppenbefehl, Zeitsteuerung im Aktor), stimmt die Anzeige dann nicht mehr. Mit einer Rückmelde-Variable folgt die Anzeige dem Gerät:

Rückmeldung | Auswertung
----------- | ----------
Integer mit einem Profil `~ShutterStatus…` | 1 = offen, 2 = geschlossen. Bei 0 (unbekannt) gilt der letzte Fahrbefehl.
Andere Integer- oder Float-Variable | Position in Prozent, 0 = ganz oben. Nur 0 gilt als offen, jeder andere Wert als geschlossen, denn dieser Gerätetyp kennt nur Auf und Zu.

Manche Aktoren melden die Position erst, wenn der Rollladen steht. Damit die Anzeige in der Zwischenzeit nicht auf den alten Zustand zurückspringt, gilt ein Fahrbefehl, der jünger ist als die Rückmeldung, bis zu 180 Sekunden lang als Ziel.

#### Mögliche Aktionen

Aktion                        | Beschreibung                              | Möglicher Satz zum Aktivieren
----------------------------- | ----------------------------------------- | -----------------------------
Rollladen/Jalousie Öffnen oder Schließen | Schaltet die Variable auf hoch (0) oder runter (4) | "Hey Siri, öffne Rolladen _<Name\>_."