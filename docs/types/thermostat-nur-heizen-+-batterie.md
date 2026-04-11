### Beschreibung

Geräte vom Typ Thermostat (Nur Heizen + Batterie) entsprechen dem Thermostat (Nur Heizen), zeigen zusätzlich den Batteriestatus in HomeKit an. Geeignet für batteriebetriebene Heizkörperthermostate wie HM-CC-RT-DN oder HmIP-eTRV-2 mit LOWBAT-Variable.

### Parameter

Name              | Beschreibung
----------------- | ---------------
Name              | Name mit dem das Gerät über HomeKit angesprochen werden kann
Soll-Variable     | Eine schaltbare Variable vom Typ Float, durch welche die Temperatur eingestellt wird
Ist-Variable      | Eine Variable vom Typ Float, durch welche die Ist-Temperatur angezeigt wird
Batterie-Variable | Eine optionale Variable vom Typ Boolean, die den LOWBAT-Status des Geräts anzeigt (true = schwache Batterie)

#### Mögliche Aktionen

Aktion                 | Beschreibung                                                             | Möglicher Satz zum Aktivieren
---------------------- | ------------------------------------------------------------------------ | -----------------------------
Temperatur einstellen  | Schalte die Soll-Variable auf die angegebene Temperatur.                 | "Hey Siri, stelle _<Name\>_ auf 21 °C."
Temperatur abfragen    | Fragt die eingestellte Temperatur oder Ist-Variable ab.                  | "Hey Siri, wie ist die Temperatur von _<Name\>_."
