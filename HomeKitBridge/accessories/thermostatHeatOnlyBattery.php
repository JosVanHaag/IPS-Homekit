<?php

declare(strict_types=1);

class HAPAccessoryThermostatHeatOnlyBattery extends HAPAccessoryBase
{
    use HelperSetDevice;

    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceThermostatHeatOnly(),
                new HAPServiceBatteryService()
            ]
        );
    }

    // --- Thermostat-Methoden (wie ThermostatHeatOnly) ---

    public function notifyCharacteristicCurrentHeatingCoolingState()
    {
        return [
            $this->data['CurrentTemperatureID'],
            $this->data['TargetTemperatureID']
        ];
    }

    public function readCharacteristicCurrentHeatingCoolingState()
    {
        if (GetValue($this->data['CurrentTemperatureID']) < GetValue($this->data['TargetTemperatureID'])) {
            return HAPCharacteristicCurrentHeatingCoolingState::Heat;
        }
        return HAPCharacteristicCurrentHeatingCoolingState::Off;
    }

    public function notifyCharacteristicTargetHeatingCoolingStateHeatOnly()
    {
        return [];
    }

    public function readCharacteristicTargetHeatingCoolingStateHeatOnly()
    {
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

    // --- Battery-Methoden ---

    public function notifyCharacteristicBatteryLevel()
    {
        return [
            $this->data['LowBatteryID']
        ];
    }

    public function readCharacteristicBatteryLevel()
    {
        // HM liefert nur bool LOWBAT — in Prozent umrechnen
        return GetValue($this->data['LowBatteryID']) ? 10 : 100;
    }

    public function notifyCharacteristicChargingState()
    {
        return [];
    }

    public function readCharacteristicChargingState()
    {
        // Thermostate sind nicht aufladbar
        return HAPCharacteristicChargingState::NotChargeable;
    }

    public function notifyCharacteristicStatusLowBattery()
    {
        return [
            $this->data['LowBatteryID']
        ];
    }

    public function readCharacteristicStatusLowBattery()
    {
        return (int) GetValue($this->data['LowBatteryID']);
    }
}

class HAPAccessoryConfigurationThermostatHeatOnlyBattery extends HAPAccessoryConfigurationThermostatHeatOnly
{
    public static function getPosition()
    {
        return 11;
    }

    public static function getCaption()
    {
        return 'Thermostat (Heat Only + Battery)';
    }

    public static function getColumns()
    {
        return array_merge(parent::getColumns(), [
            [
                'label' => 'LowBatteryID',
                'name'  => 'LowBatteryID',
                'width' => '200px',
                'add'   => 0,
                'edit'  => [
                    'type' => 'SelectVariable'
                ]
            ]
        ]);
    }

    public static function getObjectIDs($data)
    {
        return [
            $data['CurrentTemperatureID'],
            $data['TargetTemperatureID'],
            $data['LowBatteryID']
        ];
    }

    public static function getStatus($data)
    {
        // Basis-Validierung zuerst
        $rResult = parent::getStatus($data);
        if ($rResult !== 'OK') {
            return $rResult;
        }

        // LowBatteryID ist optional — nur pruefen wenn gesetzt
        if (IPS_VariableExists($data['LowBatteryID'])) {
            $oVar = IPS_GetVariable($data['LowBatteryID']);
            if ($oVar['VariableType'] != 0 /* Boolean */) {
                return 'LowBatteryID: Bool required';
            }
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        $aBase = parent::getTranslations();
        $aBase['de'] = array_merge($aBase['de'], [
            'Thermostat (Heat Only + Battery)'  => 'Thermostat (Nur Heizen + Batterie)',
            'LowBatteryID'                      => 'BatterieWarnung-ID',
            'LowBatteryID: Bool required'       => 'BatterieWarnung-ID: Bool benötigt'
        ]);
        return $aBase;
    }
}

HomeKitManager::registerAccessory('ThermostatHeatOnlyBattery');
