### Beschreibung

Geräte vom Typ Schalter (Wert) stehen für einen bestimmten Wert einer Integer-Variable, zum Beispiel für eine
Lichtszene. Der Schalter ist an, solange die Variable genau diesen Wert hat. HomeKit sieht einen gewöhnlichen
Schalter.

Werden mehrere solcher Schalter mit derselben Variable und unterschiedlichen Werten angelegt, schließen sie sich
gegenseitig aus: Wird einer eingeschaltet, zeigen die anderen „aus“. Das gilt auch, wenn die Variable außerhalb
von HomeKit geändert wird, etwa durch einen Taster oder ein Skript.

### Parameter

Name       | Beschreibung
---------- | ---------------
Name       | Name mit dem das Gerät über HomeKit angesprochen werden kann
Variable   | Eine schaltbare Variable vom Typ Integer
Wert       | Der Wert, für den dieser Schalter steht
Aus-Wert   | Der Wert, der beim Ausschalten gesetzt wird (Standard 0)

#### Mögliche Aktionen

Aktion      | Beschreibung                                                                                  | Möglicher Satz zum Aktivieren
----------- | --------------------------------------------------------------------------------------------- | -----------------------------
Einschalten | Setzt die Variable auf den Wert                                                               | "Hey Siri, schalte _<Name\>_ an."
Ausschalten | Setzt die Variable auf den Aus-Wert, aber nur, wenn der Schalter gerade an ist; sonst passiert nichts | "Hey Siri, schalte _<Name\>_ aus."
