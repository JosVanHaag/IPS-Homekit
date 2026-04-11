<?php

declare(strict_types=1);

class HAPAccessoryThermostatHeatOnly extends HAPAccessoryBase
{
    use HelperSetDevice;

    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceThermostatHeatOnly()
            ]
        );
    }

    public function notifyCharacteristicCurrentHeatingCoolingState()
    {
        return [
            $this->data['CurrentTemperatureID'],
            $this->data['TargetTemperatureID']
        ];
    }

    public function readCharacteristicCurrentHeatingCoolingState()
    {
        // Nur Off oder Heat — kein Cool, kein Auto
        if (GetValue($this->data['CurrentTemperatureID']) < GetValue($this->data['TargetTemperatureID'])) {
            return HAPCharacteristicCurrentHeatingCoolingState::Heat;
        }
        return HAPCharacteristicCurrentHeatingCoolingState::Off;
    }

    // TargetHeatingCoolingStateHeatOnly: Methodenname ergibt sich aus Klassenname via Reflection
    public function notifyCharacteristicTargetHeatingCoolingStateHeatOnly()
    {
        // Kein IPS-Objekt treibt diesen Wert — immer Heat
        return [];
    }

    public function readCharacteristicTargetHeatingCoolingStateHeatOnly()
    {
        // Thermostate koennen nur heizen
        return HAPCharacteristicTargetHeatingCoolingState::Heat;
    }

    public function writeCharacteristicTargetHeatingCoolingStateHeatOnly($value)
    {
        // Modus ist fest auf Heat — kein IPS-Eingriff
    }

    public function notifyCharacteristicCurrentTemperature()
    {
        return [
            $this->data['CurrentTemperatureID']
        ];
    }

    public function readCharacteristicCurrentTemperature()
    {
        return GetValue($this->data['CurrentTemperatureID']);
    }

    public function notifyCharacteristicTargetTemperature()
    {
        return [
            $this->data['TargetTemperatureID']
        ];
    }

    public function readCharacteristicTargetTemperature()
    {
        return GetValue($this->data['TargetTemperatureID']);
    }

    public function writeCharacteristicTargetTemperature($value)
    {
        self::setDevice($this->data['TargetTemperatureID'], floatval($value));
    }

    public function notifyCharacteristicTemperatureDisplayUnits()
    {
        return [];
    }

    public function readCharacteristicTemperatureDisplayUnits()
    {
        return HAPCharacteristicTemperatureDisplayUnits::Celsius;
    }

    public function writeCharacteristicTemperatureDisplayUnits($value)
    {
        // Einheit ist immer Celsius — kein IPS-Eingriff
    }
}

class HAPAccessoryConfigurationThermostatHeatOnly
{
    use HelperSetDevice;

    public static function getPosition()
    {
        return 10;
    }

    public static function getCaption()
    {
        return 'Thermostat (Heat Only)';
    }

    public static function getColumns()
    {
        return [
            [
                'label' => 'CurrentTemperatureID',
                'name'  => 'CurrentTemperatureID',
                'width' => '200px',
                'add'   => 0,
                'edit'  => [
                    'type' => 'SelectVariable'
                ]
            ],
            [
                'label' => 'TargetTemperatureID',
                'name'  => 'TargetTemperatureID',
                'width' => '200px',
                'add'   => 0,
                'edit'  => [
                    'type' => 'SelectVariable'
                ]
            ]
        ];
    }

    public static function getObjectIDs($data)
    {
        return [
            $data['CurrentTemperatureID'],
            $data['TargetTemperatureID']
        ];
    }

    public static function getStatus($data)
    {
        if (!IPS_VariableExists($data['CurrentTemperatureID'])) {
            return 'Variable CurrentTemperatureID missing';
        }

        if (!IPS_VariableExists($data['TargetTemperatureID'])) {
            return 'Variable TargetTemperatureID missing';
        }

        $targetVariable = IPS_GetVariable($data['CurrentTemperatureID']);

        if ($targetVariable['VariableType'] != 2 /* Float */) {
            return 'CurrentTemperatureID: Float required';
        }

        $targetVariable = IPS_GetVariable($data['TargetTemperatureID']);

        if ($targetVariable['VariableType'] != 2 /* Float */) {
            return 'TargetTemperatureID: Float required';
        }

        if ($targetVariable['VariableCustomAction'] != 0) {
            $profileAction = $targetVariable['VariableCustomAction'];
        } else {
            $profileAction = $targetVariable['VariableAction'];
        }

        if (!($profileAction > 10000)) {
            return 'TargetTemperatureID: Action required';
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        return [
            'de' => [
                'Thermostat (Heat Only)'                    => 'Thermostat (Nur Heizen)',
                'CurrentTemperatureID'                      => 'IsttemperaturID',
                'TargetTemperatureID'                       => 'SolltemperaturID',
                'Variable CurrentTemperatureID missing'     => 'Variable IsttemperaturID fehlt',
                'Variable TargetTemperatureID missing'      => 'Variable SolltemperaturID fehlt',
                'CurrentTemperatureID: Float required'      => 'IsttemperaturID: Float benoetigt',
                'TargetTemperatureID: Float required'       => 'SolltemperaturID: Float benoetigt',
                'TargetTemperatureID: Action required'      => 'SolltemperaturID: Aktion benoetigt',
                'OK'                                        => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('ThermostatHeatOnly');
