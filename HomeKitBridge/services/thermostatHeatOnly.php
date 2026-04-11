<?php

declare(strict_types=1);

// Wie HAPServiceThermostat, aber TargetHeatingCoolingState auf maxValue=1 beschraenkt
class HAPServiceThermostatHeatOnly extends HAPService
{
    public function __construct()
    {
        parent::__construct(
            0x4A,
            [
                //Required Characteristics
                new HAPCharacteristicCurrentHeatingCoolingState(),
                new HAPCharacteristicTargetHeatingCoolingStateHeatOnly(),
                new HAPCharacteristicCurrentTemperature(),
                new HAPCharacteristicTargetTemperature(),
                new HAPCharacteristicTemperatureDisplayUnits()
            ],
            [
                //Optional Characteristics
                new HAPCharacteristicCoolingThresholdTemperature(),
                new HAPCharacteristicCurrentRelativeHumidity(),
                new HAPCharacteristicHeatingThresholdTemperature(),
                new HAPCharacteristicName(),
                new HAPCharacteristicTargetRelativeHumidity()
            ]
        );
    }
}
