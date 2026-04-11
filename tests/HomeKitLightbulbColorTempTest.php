<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitLightbulbColorTempTest extends HomeKitBaseTest
{
    public function testAccessory(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $sid = IPS_CreateVariable(0 /* Boolean */); //StateID
        IPS_SetVariableCustomAction($sid, 10001); //Any valid ID will do

        $bid = IPS_CreateVariable(1 /* Integer */); //BrightnessID

        //Currently stubs do not provide default profiles
        if (!IPS_VariableProfileExists('~Intensity.100')) {
            IPS_CreateVariableProfile('~Intensity.100', 1 /* Integer */);
            IPS_SetVariableProfileValues('~Intensity.100', 0, 100, 1);
        }

        IPS_SetVariableCustomProfile($bid, '~Intensity.100'); //Any valid profile will do
        IPS_SetVariableCustomAction($bid, 10001); //Any valid ID will do

        $ctid = IPS_CreateVariable(1 /* Integer */); //ColorTemperatureID
        IPS_SetVariableCustomAction($ctid, 10001); //Any valid ID will do

        IPS_SetProperty($bridgeID, 'AccessoryLightbulbColorTemp', json_encode([
            [
                'ID'                 => 2,
                'Name'               => 'Test',
                'StateID'            => $sid,
                'BrightnessID'       => $bid,
                'ColorTemperatureID' => $ctid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/LightbulbColorTemp.json'), true);

        //Check if the generated content matches our test file
        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    public function testAccessoryBroken(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $sid = IPS_CreateVariable(0 /* Boolean */);
        //Action missing on StateID — status check will fail

        IPS_SetProperty($bridgeID, 'AccessoryLightbulbColorTemp', json_encode([
            [
                'ID'                 => 2,
                'Name'               => 'Test',
                'StateID'            => $sid,
                'BrightnessID'       => 9999, /* This is always an invalid variableID */
                'ColorTemperatureID' => 9999  /* This is always an invalid variableID */
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        //Check if the generated content matches our test file
        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }
}
