<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitThermostatHeatOnlyBatteryTest extends HomeKitBaseTest
{
    public function testAccessory(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $ctid = IPS_CreateVariable(2 /* Float */); //CurrentTemperatureID

        $ttid = IPS_CreateVariable(2 /* Float */); //TargetTemperatureID
        IPS_SetVariableCustomAction($ttid, 10001); //Any valid ID will do

        $lid = IPS_CreateVariable(0 /* Boolean */); //LowBatteryID

        IPS_SetProperty($bridgeID, 'AccessoryThermostatHeatOnlyBattery', json_encode([
            [
                'ID'                   => 2,
                'Name'                 => 'Test',
                'CurrentTemperatureID' => $ctid,
                'TargetTemperatureID'  => $ttid,
                'LowBatteryID'         => $lid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/ThermostatHeatOnlyBattery.json'), true);

        //Check if the generated content matches our test file
        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    public function testAccessoryBroken(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        IPS_SetProperty($bridgeID, 'AccessoryThermostatHeatOnlyBattery', json_encode([
            [
                'ID'                   => 2,
                'Name'                 => 'Test',
                'CurrentTemperatureID' => 9999, /* This is always an invalid variableID */
                'TargetTemperatureID'  => 9999, /* This is always an invalid variableID */
                'LowBatteryID'         => 9999  /* This is always an invalid variableID */
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        //Check if the generated content matches our test file
        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }
}
