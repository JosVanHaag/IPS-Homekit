<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitOccupancySensorTest extends HomeKitBaseTest
{
    public function testAccessory(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(0 /* Boolean */);

        IPS_SetProperty($bridgeID, 'AccessoryOccupancySensor', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/OccupancySensor.json'), true);

        //Check if the generated content matches our test file
        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    // Integer-Variable: belegt ist nur ein Wert groesser 0, negative Werte und 0 gelten als frei
    public function testAccessoryInteger(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(1 /* Integer */);

        IPS_SetProperty($bridgeID, 'AccessoryOccupancySensor', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        foreach ([-1 => 0, 0 => 0, 1 => 1, 2 => 1] as $iValue => $iExpected) {
            SetValue($vid, $iValue);
            $rDetected = $this->getOccupancyDetected($bridgeInterface->DebugAccessories());
            // Ohne Export waere null, und null == 0 gilt bei assertEquals als gleich
            $this->assertNotNull($rDetected, 'Wert ' . $iValue . ': Geraet nicht exportiert');
            $this->assertEquals($iExpected, $rDetected, 'Wert ' . $iValue);
        }
    }

    // Float-Variable wird abgewiesen
    public function testAccessoryFloatRejected(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(2 /* Float */);

        IPS_SetProperty($bridgeID, 'AccessoryOccupancySensor', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }

    // ---------------------------------------------------------------
    // Liest den Wert der Characteristic OccupancyDetected (Typ 71) aus dem Export
    // ---------------------------------------------------------------
    private function getOccupancyDetected(array $aAccessories)
    {
        foreach ($aAccessories as $dAccessory) {
            foreach ($dAccessory['services'] as $dService) {
                foreach ($dService['characteristics'] as $dCharacteristic) {
                    if ($dCharacteristic['type'] === '71') {
                        return $dCharacteristic['value'];
                    }
                }
            }
        }

        return null;
    }

    public function testAccessoryBroken(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        IPS_SetProperty($bridgeID, 'AccessoryOccupancySensor', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => 9999 /* This is always an invalid variableID */
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        //Check if the generated content matches our test file
        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }
}
