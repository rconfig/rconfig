<?php

namespace App\Http\Controllers\Connections\Telnet;

class Quit
{
    private $connectionObj;

    protected $send;

    protected $read;

    public function __construct(object $connectionObj)
    {
        $this->connectionObj = $connectionObj;
        $this->send = new Send($this->connectionObj->connection);
        $this->read = new Read($this->connectionObj);
    }

    public function closeTelnet()
    {
        dump($this->connectionObj->connection);
        $this->checkConnectionStatus();
        $this->resetPagingCmd();
        $this->sendSaveConfig();
        $this->sendExitCmd();

        fclose($this->connectionObj->connection);
    }

    private function checkConnectionStatus()
    {
        if (!$this->connectionObj->connection) {
            $logmsg = 'Telnet connection already closed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id);
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }
    }

    private function resetPagingCmd()
    {
        if (!empty($this->connectionObj->resetPagingCmd)) {
            $this->send->sendString($this->connectionObj->resetPagingCmd);
            $this->read->readTo($this->connectionObj->devicePrompt);
        }
    }

    private function sendSaveConfig()
    {
        if (!empty($this->connectionObj->saveConfig)) {
            $this->send->sendString($this->connectionObj->saveConfig);
            $this->read->readTo($this->connectionObj->devicePrompt);
        }
    }

    private function sendExitCmd()
    {
        if (!empty($this->connectionObj->exitCmd)) {
            $this->send->sendString($this->connectionObj->exitCmd);
        }
    }
}
