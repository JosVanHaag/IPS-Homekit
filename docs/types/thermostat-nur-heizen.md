### Beschreibung

Geräte vom Typ Thermostat (Nur Heizen) können Soll-Temperaturen einstellen und Ist-Temperaturen anzeigen. Im Gegensatz zum Standard-Thermostat unterstützt dieser Typ ausschließlich den Modus "Heizen" — geeignet für HM-CC-RT-DN und ähnliche Heizkörperthermostate.

### Parameter

Name           | Beschreibung
-------------- | ---------------
Name           | Name mit dem das Gerät über HomeKit angesprochen werden kann
Soll-Variable  | Eine schaltbare Variable vom Typ Float, durch welche die Temperatur eingestellt wird
Ist-Variable   | Eine Variable vom Typ Float, durch welche die Ist-Temperatur angezeigt wird

#### Mögliche Aktionen

Aktion                 | Beschreibung                                                             | Möglicher Satz zum Aktivieren
---------------------- | ------------------------------------------------------------------------ | -----------------------------
Temperatur einstellen  | Schalte die Soll-Variable auf die angegebene Temperatur.                 | "Hey Siri, stelle _<Name\>_ auf 21 °C."
Temperatur abfragen    | Fragt die eingestellte Temperatur oder Ist-Variable ab.                  | "Hey Siri, wie ist die Temperatur von _<Name\>_."
