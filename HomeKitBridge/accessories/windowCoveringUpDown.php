<?php

declare(strict_types=1);

class HAPAccessoryWindowCoveringUpDown extends HAPAccessoryBase
{
    use HelperSetDevice;

    // Befehl gilt so lange als Ziel, bis die Rueckmeldung nachzieht (Sekunden)
    public const COMMANDWINDOW = 180;

    public function __construct($data)
    {
        parent::__construct(
            $data,
            [
                new HAPServiceAccessoryInformation(),
                new HAPServiceWindowCovering()
            ]
        );
    }

    public function notifyCharacteristicTargetPosition()
    {
        return $this->getNotifyIDs();
    }

    public function readCharacteristicTargetPosition()
    {
        $statusID = $this->data['StatusID'] ?? 0;
        if ($statusID == 0) {
            return $this->readCharacteristicCurrentPosition();
        }

        // Ein Befehl, der juenger ist als die Rueckmeldung, bleibt fuer eine Weile das Ziel.
        // Manche Aktoren melden erst nach dem Halt, sonst spraenge die Anzeige zurueck.
        $commandUpdated = IPS_GetVariable($this->data['VariableID'])['VariableUpdated'];
        $statusUpdated = IPS_GetVariable($statusID)['VariableUpdated'];
        if (($commandUpdated > $statusUpdated) && (($this->getCurrentTime() - $commandUpdated) <= self::COMMANDWINDOW)) {
            switch (GetValue($this->data['VariableID'])) {
                case 0: /* Open */
                    return 100;
                case 4: /* Close */
                    return 0;
            }
        }

        return $this->readCharacteristicCurrentPosition();
    }

    public function writeCharacteristicTargetPosition($value)
    {
        if ($value > 0) {
            $this->setDevice($this->data['VariableID'], 0 /* Open */);
        } else {
            $this->setDevice($this->data['VariableID'], 4 /* Close */);
        }
    }

    public function notifyCharacteristicCurrentPosition()
    {
        return $this->getNotifyIDs();
    }

    public function readCharacteristicCurrentPosition()
    {
        $statusID = $this->data['StatusID'] ?? 0;
        if ($statusID > 0) {
            $status = GetValue($statusID);
            if (HAPAccessoryConfigurationWindowCoveringUpDown::isShutterStatus($statusID)) {
                // Status der Aktor-Rueckmeldung: 1 offen, 2 zu, 0 unbekannt
                switch ($status) {
                    case 1: /* Opened */
                        return 100;
                    case 2: /* Closed */
                        return 0;
                }
            } else {
                // Position in Prozent, 0 = ganz oben. Der Typ kennt nur auf und zu.
                return ($status == 0) ? 100 : 0;
            }
        }

        switch (GetValue($this->data['VariableID'])) {
            case 0: /* Open */
                return 100;
            case 2: /* Stop */
                return 50;
            case 4: /* Close */
                return 0;
        }

        return 50; /* Undefined. Return something... */
    }

    public function notifyCharacteristicPositionState()
    {
        return [];
    }

    public function readCharacteristicPositionState()
    {
        return HAPCharacteristicPositionState::Stopped;
    }

    public function writeCharacteristicHoldPosition($value)
    {
        if ($value) {
            $this->setDevice($this->data['VariableID'], 2 /* Stop */);
        }
    }

    protected function getCurrentTime(): int
    {
        return time();
    }

    private function getNotifyIDs(): array
    {
        $ids = [$this->data['VariableID']];
        if (($this->data['StatusID'] ?? 0) > 0) {
            $ids[] = $this->data['StatusID'];
        }
        return $ids;
    }
}

class HAPAccessoryConfigurationWindowCoveringUpDown
{
    use HelperShutterDevice;

    public static function getPosition()
    {
        return 10;
    }

    public static function getCaption()
    {
        return 'Window Covering (Up/Down)';
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
                'label' => 'StatusID',
                'name'  => 'StatusID',
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
            $data['StatusID'] ?? 0,
        ];
    }

    public static function getStatus($data)
    {
        $status = self::getShutterCompatibility($data['VariableID']);
        if ($status != 'OK') {
            return $status;
        }

        // Die Rueckmeldung ist optional und braucht keine Aktion
        $statusID = $data['StatusID'] ?? 0;
        if ($statusID == 0) {
            return 'OK';
        }

        if (!IPS_VariableExists($statusID)) {
            return 'Status variable missing';
        }

        if (!in_array(IPS_GetVariable($statusID)['VariableType'], [1 /* Integer */, 2 /* Float */])) {
            return 'Status variable: Integer/Float required';
        }

        return 'OK';
    }

    // Erkennt eine Status-Rueckmeldung (offen/zu) am Profil, alles andere gilt als Prozentposition
    public static function isShutterStatus($variableID)
    {
        $targetVariable = IPS_GetVariable($variableID);

        if (function_exists('IPS_GetVariablePresentation')) {
            $presentation = IPS_GetVariablePresentation($variableID);
            $profileName = $presentation['PROFILE'] ?? '';
        } elseif ($targetVariable['VariableCustomProfile'] != '') {
            $profileName = $targetVariable['VariableCustomProfile'];
        } else {
            $profileName = $targetVariable['VariableProfile'];
        }

        return ($targetVariable['VariableType'] == 1 /* Integer */) && (strpos($profileName, '~ShutterStatus') === 0);
    }

    public static function getTranslations()
    {
        return [
            'de' => [
                'Window Covering (Up/Down)'               => 'Rollladen/Jalousie (Hoch/Runter)',
                'VariableID'                              => 'VariablenID',
                'StatusID'                                => 'Rückmeldung',
                'Status variable missing'                 => 'Rückmeldung fehlt',
                'Status variable: Integer/Float required' => 'Rückmeldung: Integer/Float benötigt',
                'Variable missing'                        => 'Variable fehlt',
                'Int required'                            => 'Int benötigt',
                'Profile required'                        => 'Profil benötigt',
                'Unsupported Profile'                     => 'Falsches Profil',
                'OK'                                      => 'OK'
            ]
        ];
    }
}

HomeKitManager::registerAccessory('WindowCoveringUpDown');
