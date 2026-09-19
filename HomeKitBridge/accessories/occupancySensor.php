<?php

declare(strict_types=1);

class HAPAccessoryOccupancySensor extends HAPAccessoryBase
{
    use HelperSwitchDevice;

    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceOccupancySensor()
            ]
        );
    }

    public function notifyCharacteristicOccupancyDetected()
    {
        return [
            $this->data['VariableID']
        ];
    }

    // OccupancyDetected ist uint8, kein bool — int-Cast erforderlich
    public function readCharacteristicOccupancyDetected()
    {
        // Integer-Variable: belegt nur bei Wert groesser 0 (z. B. -1 abwesend, 0 unbestimmt, 1 anwesend)
        if (IPS_GetVariable($this->data['VariableID'])['VariableType'] == 1 /* Integer */) {
            return (GetValue($this->data['VariableID']) > 0) ? 1 : 0;
        }

        return (int) self::GetSwitchValue($this->data['VariableID']);
    }
}

class HAPAccessoryConfigurationOccupancySensor
{
    public static function getPosition()
    {
        return 10;
    }

    public static function getCaption()
    {
        return 'Occupancy Sensor';
    }

    public static function getColumns()
    {
        return [
            [
                'label' => 'VariableID',
                'name'  => 'VariableID',
                'width' => '250px',
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
            $data['VariableID'],
        ];
    }

    public static function getStatus($data)
    {
        if (!IPS_VariableExists($data['VariableID'])) {
            return 'Variable missing';
        }

        $targetVariable = IPS_GetVariable($data['VariableID']);

        if (!in_array($targetVariable['VariableType'], [0 /* Boolean */, 1 /* Integer */])) {
            return 'Bool or Integer required';
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        return [
            'de' => [
                'Occupancy Sensor'  => 'Präsenzmelder',
                'VariableID'        => 'VariablenID',
                'Variable missing'  => 'Variable fehlt',
                'Bool or Integer required' => 'Bool oder Integer benötigt',
                'OK'                => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('OccupancySensor');
