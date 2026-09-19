<?php

declare(strict_types=1);

include_once __DIR__ . '/HomeKitBaseTest.php';

class HomeKitSessionNotifyTest extends HomeKitBaseTest
{
    private $clientIP = '127.0.0.1';
    private $clientPort = 34456;

    // Schluessel der Testsitzung: Controller -> Bridge und Bridge -> Controller
    private $recvKey = '';
    private $sendKey = '';

    // Nachrichtenzaehler aus Sicht des Controllers
    private $controllerSendCounter = 0;
    private $controllerRecvCounter = 0;

    // Ein Schaltbefehl, der die Variable synchron aendert, loest in IP-Symcon
    // MessageSink verschachtelt im laufenden ReceiveData aus. Der Controller,
    // der den Befehl geschickt hat, muss danach eine gueltig verschluesselte
    // Antwort bekommen und darf keine Meldung zu seiner eigenen Aenderung erhalten
    public function testWriteWithSynchronousChangeKeepsSessionIntact(): void
    {
        list($bridgeID, $serverID, $vid) = $this->createBridgeWithDimmer();

        $serverInterface = IPS\InstanceManager::getInstanceInterface($serverID);

        $this->subscribeDimmer($serverInterface);

        // Einschalten: das Aktionsscript aendert die Variable sofort
        $this->pushEncrypted($serverInterface, $this->buildPut('{"characteristics":[{"aid":2,"iid":102,"value":1}]}'));

        $aMessages = $this->readAllMessages($serverInterface);

        // Alles, was zurueckkommt, muss mit fortlaufenden Zaehlern entschluesselbar sein
        $this->assertNotContains(false, $aMessages, 'Antwort mit doppelt vergebenem Zaehler');

        // Genau eine Antwort: die Bestaetigung des Befehls, keine Meldung an den Ausloeser
        $this->assertCount(1, $aMessages);
        $this->assertStringStartsWith('HTTP/1.1 204 No Content', $aMessages[0]);

        // Der Befehl hat gewirkt
        $this->assertEquals(100, GetValue($vid));
    }

    // Aenderungen, die nicht vom Controller selbst ausgehen, werden weiterhin gemeldet
    public function testExternalChangeIsStillNotified(): void
    {
        list($bridgeID, $serverID, $vid) = $this->createBridgeWithDimmer();

        $serverInterface = IPS\InstanceManager::getInstanceInterface($serverID);
        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);

        $this->subscribeDimmer($serverInterface);

        // Aenderung von aussen, zum Beispiel ueber einen Wandtaster
        SetValue($vid, 40);
        $bridgeInterface->MessageSink(time(), $vid, VM_UPDATE, [40, true, 0, time()]);

        $aMessages = $this->readAllMessages($serverInterface);

        $this->assertNotContains(false, $aMessages);
        $this->assertCount(1, $aMessages);
        $this->assertStringStartsWith('EVENT/1.0 200 OK', $aMessages[0]);
        $this->assertStringContainsString('"value":40', $aMessages[0]);
    }

    // ---------------------------------------------------------------
    // Legt Bridge, Dimmer-Variable mit Aktionsscript und eine
    // verschluesselte Sitzung fuer den Test-Controller an
    // ---------------------------------------------------------------
    private function createBridgeWithDimmer(): array
    {
        $bridgeID = IPS_CreateInstance($this->bridgeModuleID);

        $vid = IPS_CreateVariable(1 /* Integer */);

        //Currently stubs do not provide default profiles
        if (!IPS_VariableProfileExists('~Intensity.100')) {
            IPS_CreateVariableProfile('~Intensity.100', 1 /* Integer */);
            IPS_SetVariableProfileValues('~Intensity.100', 0, 100, 1);
        }
        IPS_SetVariableCustomProfile($vid, '~Intensity.100');

        // Aktionsscript wie ein Geraetemodul: Wert sofort setzen, und IP-Symcon
        // stellt die Aenderung dabei synchron an MessageSink der Bridge zu
        $sid = IPS_CreateScript(0 /* PHP */);
        IPS_SetScriptContent($sid, '<?php
            $iOld = GetValue($_IPS["VARIABLE"]);
            SetValue($_IPS["VARIABLE"], $_IPS["VALUE"]);
            IPS\InstanceManager::getInstanceInterface(' . $bridgeID . ')->MessageSink(time(), $_IPS["VARIABLE"], VM_UPDATE, [$_IPS["VALUE"], true, $iOld, time()]);
        ');
        IPS_SetVariableCustomAction($vid, $sid);

        IPS_SetProperty($bridgeID, 'AccessoryLightbulbDimmer', json_encode([
            [
                'ID'         => 2,
                'Name'       => 'Test',
                'VariableID' => $vid
            ]
        ]));
        IPS_ApplyChanges($bridgeID);

        $serverID = IPS_GetInstance($bridgeID)['ConnectionID'];

        // Verschluesselte Sitzung direkt vorgeben, die Kopplung selbst ist hier nicht Thema
        $this->recvKey = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES);
        $this->sendKey = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES);
        $this->controllerSendCounter = 0;
        $this->controllerRecvCounter = 0;

        $sSession = json_encode([
            'locked'             => false,
            'data'               => '',
            'identifier'         => 'test-controller',
            'events'             => [],
            'encrypted'          => true,
            'encryptedData'      => '',
            'messageRecvKey'     => bin2hex($this->recvKey),
            'messageRecvCounter' => 0,
            'messageSendKey'     => bin2hex($this->sendKey),
            'messageSendCounter' => 0,
            'setupCode'          => '',
            'salt'               => '',
            'privateValue'       => '',
            'publicValue'        => '',
            'sharedSecret'       => '',
            'sessionKey'         => '',
        ]);

        $bridgeInterface = IPS\InstanceManager::getInstanceInterface($bridgeID);
        $sBufferName = $this->clientIP . ':' . $this->clientPort;
        $fnSetBuffer = Closure::bind(function ($sName, $sData)
        {
            $this->SetBuffer($sName, $sData);
        }, $bridgeInterface, get_class($bridgeInterface));
        $fnSetBuffer($sBufferName, $sSession);

        IPS\InstanceManager::getInstanceInterface($serverID)->PushConnect($this->clientIP, $this->clientPort);

        return [$bridgeID, $serverID, $vid];
    }

    // ---------------------------------------------------------------
    // Abonniert On und Brightness des Dimmers und prueft die Bestaetigung
    // ---------------------------------------------------------------
    private function subscribeDimmer($serverInterface): void
    {
        $this->pushEncrypted($serverInterface, $this->buildPut('{"characteristics":[{"aid":2,"iid":102,"ev":true},{"aid":2,"iid":103,"ev":true}]}'));

        $aMessages = $this->readAllMessages($serverInterface);
        $this->assertCount(1, $aMessages);
        $this->assertStringStartsWith('HTTP/1.1 204 No Content', $aMessages[0]);
    }

    // ---------------------------------------------------------------
    // Baut einen HTTP-PUT auf /characteristics
    // ---------------------------------------------------------------
    private function buildPut(string $sBody): string
    {
        return "PUT /characteristics HTTP/1.1\r\n" .
            "Host: Test._hap._tcp.local\r\n" .
            'Content-Length: ' . strlen($sBody) . "\r\n" .
            "Content-Type: application/hap+json\r\n" .
            "\r\n" .
            $sBody;
    }

    // ---------------------------------------------------------------
    // Verschluesselt eine Anfrage wie ein Controller und schickt sie an die Bridge
    // ---------------------------------------------------------------
    private function pushEncrypted($serverInterface, string $sPlain): void
    {
        $yLength = pack('v', strlen($sPlain));
        $yNonce = "\0\0\0\0" . pack('P', $this->controllerSendCounter);
        $yCipher = sodium_crypto_aead_chacha20poly1305_ietf_encrypt($sPlain, $yLength, $yNonce, $this->recvKey);
        $this->controllerSendCounter++;

        $serverInterface->PushPacket($yLength . $yCipher, $this->clientIP, $this->clientPort);
    }

    // ---------------------------------------------------------------
    // Holt alle Pakete an den Test-Controller und entschluesselt sie mit
    // fortlaufenden Zaehlern; ein nicht entschluesselbarer Rahmen wird false
    // ---------------------------------------------------------------
    private function readAllMessages($serverInterface): array
    {
        $aMessages = [];

        while ($serverInterface->HasPacket()) {
            $dPacket = $serverInterface->PopPacket();
            if ($dPacket['Type'] !== 0 || $dPacket['ClientPort'] !== $this->clientPort) {
                continue;
            }

            $yData = $dPacket['Buffer'];
            $sMessage = '';
            while (strlen($yData) >= 2) {
                $iLength = unpack('v', $yData)[1];
                $yFrame = substr($yData, 2, $iLength + 16);
                $yNonce = "\0\0\0\0" . pack('P', $this->controllerRecvCounter);
                $rPlain = sodium_crypto_aead_chacha20poly1305_ietf_decrypt($yFrame, substr($yData, 0, 2), $yNonce, $this->sendKey);
                $this->controllerRecvCounter++;
                if ($rPlain === false) {
                    $sMessage = false;
                    break;
                }
                $sMessage .= $rPlain;
                $yData = substr($yData, 2 + $iLength + 16);
            }
            $aMessages[] = $sMessage;
        }

        return $aMessages;
    }
}
