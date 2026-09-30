<?php

declare(strict_types=1);

// Schalter, der fuer einen Wert einer Integer-Variable steht (z. B. eine Szene).
// Mehrere solcher Schalter auf derselben Variable schliessen sich gegenseitig aus,
// weil jeder seinen Zustand aus dem aktuellen Wert liest.
class HAPAccessorySwitchValue extends HAPAccessoryBase
{
    use HelperSetDevice;

    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceSwitch()
            ]
        );
    }

    public function notifyCharacteristicOn()
    {
        return [
            $this->data['VariableID']
        ];
    }

    public function readCharacteristicOn()
    {
        return GetValue($this->data['VariableID']) === intval($this->data['Value']);
    }

    public function writeCharacteristicOn($value)
    {
        if ($value) {
            self::setDevice($this->data['VariableID'], intval($this->data['Value']));
            return;
        }

        // Ausschalten wirkt nur, wenn dieser Schalter gerade aktiv ist. Sonst wuerde
        // das Ausschalten eines inaktiven Schalters den aktiven Wert ueberschreiben.
        if ($this->readCharacteristicOn()) {
            self::setDevice($this->data['VariableID'], intval($this->data['OffValue'] ?? 0));
        }
    }
}

class HAPAccessoryConfigurationSwitchValue
{
    public static function getPosition()
    {
        return 11;
    }

    public static function getCaption()
    {
        return 'Switch (Value)';
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
            ],
            [
                'label' => 'Value',
                'name'  => 'Value',
                'width' => '100px',
                'add'   => 1,
                'edit'  => [
                    'type' => 'NumberSpinner'
                ]
            ],
            [
                'label' => 'Off value',
                'name'  => 'OffValue',
                'width' => '100px',
                'add'   => 0,
                'edit'  => [
                    'type' => 'NumberSpinner'
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

        if ($targetVariable['VariableType'] != 1 /* Integer */) {
            return 'Int required';
        }

        if (!HasAction($data['VariableID'])) {
            return 'Action required';
        }

        return 'OK';
    }

    public static function getTranslations()
    {
        return [
            'de' => [
                'Switch (Value)'   => 'Schalter (Wert)',
                'VariableID'       => 'VariablenID',
                'Value'            => 'Wert',
                'Off value'        => 'Aus-Wert',
                'Variable missing' => 'Variable fehlt',
                'Int required'     => 'Int benötigt',
                'Action required'  => 'Aktion benötigt',
                'OK'               => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('SwitchValue');
