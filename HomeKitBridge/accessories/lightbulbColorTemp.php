<?php

declare(strict_types=1);

// LightbulbExpert zuerst laden (alphabetisch vor diesem File)
include_once __DIR__ . '/lightbulbExpert.php';

// Lampe mit separaten State/Brightness-Variablen und Farbtemperatur-Steuerung
// Fuer Z2M-Lampen (innr RCL 232 C, Hue) mit Mired-Farbtemperaturvariable
class HAPAccessoryLightbulbColorTemp extends HAPAccessoryLightbulbExpert
{
    // ColorTemperature ist optional in HAPServiceLightbulb — wird aktiviert da Methoden vorhanden
    public function notifyCharacteristicColorTemperature()
    {
        return [
            $this->data['ColorTemperatureID']
        ];
    }

    public function readCharacteristicColorTemperature()
    {
        return intval(GetValue($this->data['ColorTemperatureID']));
    }

    public function writeCharacteristicColorTemperature($value)
    {
        RequestAction($this->data['ColorTemperatureID'], intval($value));
    }
}

class HAPAccessoryConfigurationLightbulbColorTemp extends HAPAccessoryConfigurationLightbulbExpert
{
    public static function getPosition()
    {
        return 5;
    }

    public static function getCaption()
    {
        return 'Lightbulb (Color Temp)';
    }

    public static function getColumns()
    {
        return array_merge(parent::getColumns(), [
            [
                'label' => 'ColorTemperatureID',
                'name'  => 'ColorTemperatureID',
                'width' => '250px',
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
            $data['StateID'],
            $data['BrightnessID'],
            $data['ColorTemperatureID']
        ];
    }

    public static function getStatus($data)
    {
        // State + Brightness via parent pruefen
        $rResult = parent::getStatus($data);
        if ($rResult !== 'OK') {
            return $rResult;
        }

        if (!IPS_VariableExists($data['ColorTemperatureID'])) {
            return 'Variable ColorTemperatureID missing';
        }

        $oVar = IPS_GetVariable($data['ColorTemperatureID']);

        if ($oVar['VariableType'] != 1 /* Integer */) {
            return 'ColorTemperatureID: Int required';
        }

        $iAction = $oVar['VariableCustomAction'] != 0
            ? $oVar['VariableCustomAction']
            : $oVar['VariableAction'];

        if (!($iAction > 10000)) {
            return 'ColorTemperatureID: Action required';
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        $aBase = parent::getTranslations();
        $aBase['de'] = array_merge($aBase['de'], [
            'Lightbulb (Color Temp)'                => 'Lampe (Farbtemperatur)',
            'ColorTemperatureID'                    => 'FarbtemperaturID',
            'Variable ColorTemperatureID missing'   => 'Variable FarbtemperaturID fehlt',
            'ColorTemperatureID: Int required'      => 'FarbtemperaturID: Int benoetigt',
            'ColorTemperatureID: Action required'   => 'FarbtemperaturID: Aktion benoetigt'
        ]);
        return $aBase;
    }
}

HomeKitManager::registerAccessory('LightbulbColorTemp');
