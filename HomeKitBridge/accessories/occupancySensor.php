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

        if ($targetVariable['VariableType'] != 0 /* Boolean */) {
            return 'Bool required';
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        return [
            'de' => [
                'Occupancy Sensor'  => 'Praesenzmelder',
                'VariableID'        => 'VariablenID',
                'Variable missing'  => 'Variable fehlt',
                'Bool required'     => 'Bool benoetigt',
                'OK'                => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('OccupancySensor');
