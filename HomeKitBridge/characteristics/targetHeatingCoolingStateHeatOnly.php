<?php

declare(strict_types=1);

// Subklasse von TargetHeatingCoolingState mit maxValue=1 (nur Off=0, Heat=1)
// Damit zeigt HomeKit nur Off/Heizen, kein Kuehlen/Auto
class HAPCharacteristicTargetHeatingCoolingStateHeatOnly extends HAPCharacteristicTargetHeatingCoolingState
{
    public function __construct()
    {
        HAPCharacteristic::__construct(
            0x33,
            HAPCharacteristicFormat::UnsignedInt8,
            [
                HAPCharacteristicPermission::PairedRead,
                HAPCharacteristicPermission::PairedWrite,
                HAPCharacteristicPermission::Notify
            ],
            0,
            1,
            1
        );
    }
}
