<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitWindowCoveringUpDownTest extends HomeKitBaseTest
{
    public function testAccessory(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(1 /* Integer */);

        //Currently stubs do not provide default profiles
        if (!IPS_VariableProfileExists('~ShutterMoveStop')) {
            IPS_CreateVariableProfile('~ShutterMoveStop', 1 /* Integer */);
        }

        IPS_SetVariableCustomProfile($vid, '~ShutterMoveStop');
        IPS_SetVariableCustomAction($vid, 10001); //Any valid ID will do

        IPS_SetProperty($bridgeID, 'AccessoryWindowCoveringUpDown', json_encode([
            [
                'ID'                    => 3,
                'Name'                  => 'Test',
                'VariableID'            => $vid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/WindowCoveringUpDown.json'), true);

        //Check if the generated content matches our test file
        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    // Mit gueltiger Rueckmeldung bleibt der Export unveraendert, die Spalte aendert nur die Werte
    public function testAccessoryWithStatus(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = $this->createShutterStatusVariable();

        IPS_SetProperty($bridgeID, 'AccessoryWindowCoveringUpDown', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid,
                'StatusID'   => $sid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $base = json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true);
        $accessory = json_decode(file_get_contents(__DIR__ . '/exports/WindowCoveringUpDown.json'), true);

        $this->assertEquals(array_merge($base, $accessory), $bridgeInterface->DebugAccessories());
    }

    // Eine Rueckmeldung vom Typ String wird abgewiesen, das Geraet erscheint nicht
    public function testAccessoryStatusStringRejected(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = IPS_CreateVariable(3 /* String */);

        IPS_SetProperty($bridgeID, 'AccessoryWindowCoveringUpDown', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid,
                'StatusID'   => $sid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }

    // Eine fehlende Rueckmelde-Variable wird abgewiesen, das Geraet erscheint nicht
    public function testAccessoryStatusMissingRejected(): void
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();

        IPS_SetProperty($bridgeID, 'AccessoryWindowCoveringUpDown', json_encode([
            [
                'ID'         => 3,
                'Name'       => 'Test',
                'VariableID' => $vid,
                'StatusID'   => 9999 /* This is always an invalid variableID */
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->assertEquals(json_decode(file_get_contents(__DIR__ . '/exports/None.json'), true), $bridgeInterface->DebugAccessories());
    }

    // Ohne Rueckmeldung gilt weiter die Ableitung aus dem letzten Befehl
    public function testPositionWithoutStatus(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $accessory = new HAPAccessoryWindowCoveringUpDown(['ID' => 3, 'Name' => 'Test', 'VariableID' => $vid]);

        foreach ([0 => 100, 2 => 50, 4 => 0] as $iAction => $iExpected) {
            SetValue($vid, $iAction);
            $this->assertSame($iExpected, $accessory->readCharacteristicCurrentPosition());
            $this->assertSame($iExpected, $accessory->readCharacteristicTargetPosition());
        }

        $this->assertSame([$vid], $accessory->notifyCharacteristicCurrentPosition());
        $this->assertSame([$vid], $accessory->notifyCharacteristicTargetPosition());
    }

    // Status-Rueckmeldung: 1 offen, 2 zu, 0 unbekannt faellt auf den letzten Befehl zurueck
    public function testPositionFromShutterStatus(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = $this->createShutterStatusVariable();
        $accessory = new HAPAccessoryWindowCoveringUpDown(['ID' => 3, 'Name' => 'Test', 'VariableID' => $vid, 'StatusID' => $sid]);

        // Letzter Befehl "zu", die Rueckmeldung entscheidet trotzdem
        SetValue($vid, 4);
        SetValue($sid, 1);
        $this->assertSame(100, $accessory->readCharacteristicCurrentPosition());
        SetValue($sid, 2);
        $this->assertSame(0, $accessory->readCharacteristicCurrentPosition());
        SetValue($sid, 0);
        $this->assertSame(0, $accessory->readCharacteristicCurrentPosition());

        $this->assertSame([$vid, $sid], $accessory->notifyCharacteristicCurrentPosition());
        $this->assertSame([$vid, $sid], $accessory->notifyCharacteristicTargetPosition());
    }

    // Prozent-Rueckmeldung (0 = ganz oben): nur 0 gilt als offen, jeder andere Wert als zu
    public function testPositionFromPercent(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = IPS_CreateVariable(1 /* Integer */);
        $fid = IPS_CreateVariable(2 /* Float */);
        $accessory = new HAPAccessoryWindowCoveringUpDown(['ID' => 3, 'Name' => 'Test', 'VariableID' => $vid, 'StatusID' => $sid]);
        $accessoryFloat = new HAPAccessoryWindowCoveringUpDown(['ID' => 4, 'Name' => 'Test', 'VariableID' => $vid, 'StatusID' => $fid]);

        foreach ([0 => 100, 1 => 0, 37 => 0, 100 => 0] as $iPercent => $iExpected) {
            SetValue($sid, $iPercent);
            SetValue($fid, (float) $iPercent);
            $this->assertSame($iExpected, $accessory->readCharacteristicCurrentPosition());
            $this->assertSame($iExpected, $accessoryFloat->readCharacteristicCurrentPosition());
        }
    }

    // Ein Befehl, der juenger ist als die Rueckmeldung, bestimmt fuer eine Weile die Zielposition
    public function testTargetFollowsRecentCommand(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = $this->createShutterStatusVariable();
        $data = ['ID' => 3, 'Name' => 'Test', 'VariableID' => $vid, 'StatusID' => $sid];

        SetValue($sid, 2);
        // Zeitstempel sind sekundengenau, der Befehl muss spaeter liegen
        sleep(1);
        SetValue($vid, 0);

        $accessory = new HAPAccessoryWindowCoveringUpDown($data);
        $this->assertSame(0, $accessory->readCharacteristicCurrentPosition());
        $this->assertSame(100, $accessory->readCharacteristicTargetPosition());

        // Stopp als letzter Befehl: das Ziel ist die aktuelle Position
        SetValue($vid, 2);
        $this->assertSame(0, $accessory->readCharacteristicTargetPosition());

        // Nach Ablauf des Fensters zaehlt wieder nur die Rueckmeldung
        SetValue($vid, 0);
        $accessoryLater = $this->createAccessoryAt($data, time() + 181);
        $this->assertSame(0, $accessoryLater->readCharacteristicTargetPosition());
    }

    // Ist die Rueckmeldung juenger als der Befehl, folgt das Ziel der Rueckmeldung
    public function testTargetFollowsNewerStatus(): void
    {
        IPS_CreateInstance($this->bridgeModuleID);

        $vid = $this->createActionVariable();
        $sid = $this->createShutterStatusVariable();
        $accessory = new HAPAccessoryWindowCoveringUpDown(['ID' => 3, 'Name' => 'Test', 'VariableID' => $vid, 'StatusID' => $sid]);

        SetValue($vid, 0);
        sleep(1);
        SetValue($sid, 2);

        $this->assertSame(0, $accessory->readCharacteristicTargetPosition());
    }

    // ---------------------------------------------------------------
    // Legt eine schaltbare Fahrvariable mit ~ShutterMoveStop an
    // ---------------------------------------------------------------
    private function createActionVariable(): int
    {
        $vid = IPS_CreateVariable(1 /* Integer */);

        if (!IPS_VariableProfileExists('~ShutterMoveStop')) {
            IPS_CreateVariableProfile('~ShutterMoveStop', 1 /* Integer */);
        }

        IPS_SetVariableCustomProfile($vid, '~ShutterMoveStop');
        IPS_SetVariableCustomAction($vid, 10001); //Any valid ID will do

        return $vid;
    }

    // ---------------------------------------------------------------
    // Legt eine Status-Rueckmeldung mit ~ShutterStatus.KNX an (ohne Aktion)
    // ---------------------------------------------------------------
    private function createShutterStatusVariable(): int
    {
        $sid = IPS_CreateVariable(1 /* Integer */);

        if (!IPS_VariableProfileExists('~ShutterStatus.KNX')) {
            IPS_CreateVariableProfile('~ShutterStatus.KNX', 1 /* Integer */);
        }

        IPS_SetVariableCustomProfile($sid, '~ShutterStatus.KNX');

        return $sid;
    }

    // ---------------------------------------------------------------
    // Erzeugt das Geraet mit fester Uhrzeit, um das Befehlsfenster zu pruefen
    // ---------------------------------------------------------------
    private function createAccessoryAt(array $data, int $now): HAPAccessoryWindowCoveringUpDown
    {
        return new class($data, $now) extends HAPAccessoryWindowCoveringUpDown {
            private $now;

            public function __construct($data, int $now)
            {
                parent::__construct($data);
                $this->now = $now;
            }

            protected function getCurrentTime(): int
            {
                return $this->now;
            }
        };
    }
}
