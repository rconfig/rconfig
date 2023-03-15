<?php

namespace App\Http\Controllers\Connections\Telnet;

use App\CustomClasses\SetDeviceStatus;

class Login
{
    protected $send;

    protected $read;

    protected $connectionObj;

    public function __construct(object $connectionObj)
    {
        // dd($connectionObj);
        $this->connectionObj = $connectionObj;
        $this->send = new Send($this->connectionObj->connection);
        $this->read = new Read($this->connectionObj);
    }

    public function login()
    {

        $this->read->readTo($this->connectionObj->usernamePrompt);
        $this->send->sendString($this->connectionObj->username);
        $this->read->readTo($this->connectionObj->passwordPrompt);
        $this->send->sendString($this->connectionObj->password);

        if ($this->connectionObj->enable == 'on') {
            $devicePromptValid = $this->enableModeLogin();
        } else {
            $devicePromptValid = $this->read->readTo($this->connectionObj->devicePrompt);
        }

        // valid prompt check - must be located here
        if (!preg_match('/(' . $this->connectionObj->devicePrompt . ')/i', $devicePromptValid)) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Prompt not did not match for device within timeout - This can cause slower config downloads. Device ID: ' . $this->connectionObj->device_id, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');
        }

        $this->send->sendString($this->connectionObj->pagingCmd);
        $this->read->readTo($this->connectionObj->devicePrompt);

        return $this->loginErrorCheck($devicePromptValid);
    }

    private function loginErrorCheck($devicePromptValid)
    {
        if ($devicePromptValid != true) {
            (new SetDeviceStatus($this->connectionObj->device_id, 0))->setDeviceStatus();
            $logmsg = 'Authentication Failed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }

        return true;
    }

    private function enableModeLogin()
    {
        // ADD HP LOGIN HERE AFTER
        // dd($this->connectionObj);
        $this->read->readTo($this->connectionObj->enablePrompt);
        $this->send->sendString($this->connectionObj->enableCmd);
        $this->read->readTo($this->connectionObj->enablePassPrmpt);
        $this->send->sendString($this->connectionObj->enableModePassword);

        return $this->read->readTo($this->connectionObj->devicePrompt);
    }
}
