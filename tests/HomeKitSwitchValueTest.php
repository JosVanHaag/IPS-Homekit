<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitSwitchValueTest extends HomeKitBaseTest
{
    public function testAccessory(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();

        IPS_SetProperty($bridgeID, 'AccessorySwitchValue', json_encode([
            [
                'ID'         => 2,
                'Name'       => 'Test',
                'VariableID' => $vid,
                'Value'      => 3,
                'OffValue'   => 0
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/SwitchValue.json'), true);

        //Check if the generated content matches our test file
        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    // Ohne Aktion erscheint das Geraet nicht
    public function testAccessoryBroken(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(1 /* Integer */);

        IPS_SetProperty($bridgeID, 'AccessorySwitchValue', json_encode([
            [
                'ID'         => 2,
                'Name'       => 'Test',
                'VariableID' => $vid, /* The action is missing */
                'Value'      => 1,
                'OffValue'   => 0
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }

    // Eine Bool-Variable wird abgewiesen, dafuer gibt es den einfachen Schalter
    public function testAccessoryBooleanRejected(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(0 /* Boolean */);
        IPS_SetVariableCustomAction($vid, 10001); //Any valid ID will do

        IPS_SetProperty($bridgeID, 'AccessorySwitchValue', json_encode([
            [
                'ID'         => 2,
                'Name'       => 'Test',
                'VariableID' => $vid,
                'Value'      => 1,
                'OffValue'   => 0
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }

    // Der Schalter ist an, solange die Variable seinen Wert hat
    public function testReadFollowsValue(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessorySwitchValue(['ID' => 2, 'Name' => 'Test', 'VariableID' => $vid, 'Value' => 3, 'OffValue' => 0]);

        foreach ([0 => false, 1 => false, 3 => true, 4 => false] as $iValue => $bExpected) {
            SetValue($vid, $iValue);
            $this->assertSame($bExpected, $accessory->readCharacteristicOn());
        }

        $this->assertSame([$vid], $accessory->notifyCharacteristicOn());
    }

    // Einschalten setzt den Wert ueber die Aktion der Variable
    public function testWriteOnSetsValue(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessorySwitchValue(['ID' => 2, 'Name' => 'Test', 'VariableID' => $vid, 'Value' => 3, 'OffValue' => 0]);

        SetValue($vid, 1);
        $accessory->writeCharacteristicOn(true);

        $this->assertSame(3, GetValue($vid));
        $this->assertTrue($accessory->readCharacteristicOn());
    }

    // Ausschalten des aktiven Schalters setzt den Aus-Wert
    public function testWriteOffWhenActiveSetsOffValue(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessorySwitchValue(['ID' => 2, 'Name' => 'Test', 'VariableID' => $vid, 'Value' => 3, 'OffValue' => 7]);

        SetValue($vid, 3);
        $accessory->writeCharacteristicOn(false);

        $this->assertSame(7, GetValue($vid));
        $this->assertFalse($accessory->readCharacteristicOn());
    }

    // Ausschalten eines inaktiven Schalters laesst die Variable unveraendert
    public function testWriteOffWhenInactiveKeepsValue(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessorySwitchValue(['ID' => 2, 'Name' => 'Test', 'VariableID' => $vid, 'Value' => 3, 'OffValue' => 0]);

        SetValue($vid, 2);
        $accessory->writeCharacteristicOn(false);

        $this->assertSame(2, GetValue($vid));
    }

    // Ohne Aus-Wert in der Konfiguration gilt 0
    public function testOffValueDefaultsToZero(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessorySwitchValue(['ID' => 2, 'Name' => 'Test', 'VariableID' => $vid, 'Value' => 3]);

        SetValue($vid, 3);
        $accessory->writeCharacteristicOn(false);

        $this->assertSame(0, GetValue($vid));
    }

    // Integer-Variable mit Aktionsscript, das den Wert selbst setzt
    private function createActionVariable(): int
    {
        $vid = IPS_CreateVariable(1 /* Integer */);

        $sid = IPS_CreateScript(0 /* PHP */);
        IPS_SetScriptContent($sid, '<?php SetValue($_IPS["VARIABLE"], $_IPS["VALUE"]);');
        IPS_SetVariableCustomAction($vid, $sid);

        return $vid;
    }
}
