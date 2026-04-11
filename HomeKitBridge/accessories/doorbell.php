<?php

declare(strict_types=1);

class HAPAccessoryDoorbell extends HAPAccessoryBase
{
    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceDoorbell()
            ]
        );
    }

    public function notifyCharacteristicProgrammableSwitchEvent()
    {
        return [
            $this->data['VariableID']
        ];
    }

    public function readCharacteristicProgrammableSwitchEvent()
    {
        // Doorbell fires always as SinglePress — HAP-Notification triggers the iOS push
        return HAPCharacteristicProgrammableSwitchEvent::SinglePress;
    }
}

class HAPAccessoryConfigurationDoorbell
{
    public static function getPosition()
    {
        return 260;
    }

    public static function getCaption()
    {
        return 'Doorbell';
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
                'Doorbell'         => 'Türklingel',
                'VariableID'       => 'VariablenID',
                'Variable missing' => 'Variable fehlt',
                'Bool required'    => 'Bool benötigt',
                'OK'               => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('Doorbell');
