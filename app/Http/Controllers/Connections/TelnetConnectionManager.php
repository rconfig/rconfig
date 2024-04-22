<?php

namespace App\Http\Controllers\Connections;

use App\CustomClasses\SetDeviceStatus;
use App\Http\Controllers\Connections\Telnet\Connect;
use App\Http\Controllers\Connections\Telnet\Login;
use App\Http\Controllers\Connections\Telnet\Logout;
use App\Http\Controllers\Connections\Telnet\SendCommand;

class TelnetConnectionManager
{
    protected $deviceParamsObject;

    protected $connectionObj;

    protected $loginObj;

    protected $SendCommandObj;

    protected $debug;

    public function __construct($deviceParamsObject, $debug)
    {
        $this->deviceParamsObject = $deviceParamsObject;
        $this->debug = $debug;
    }

    public function telnetConnectionAndOutput()
    {
        if ($this->loadTelnetConnect() === false) {
            return false;
        }

        if ($this->loadTelnetLogin() === false) {
            return false;
        }

        $this->SendCommandObj = new SendCommand($this->connectionObj);
        $outputArray = $this->sendTelnetCommand();

        $this->loadTelnetLogout();

        return $outputArray;
    }

    private function loadTelnetConnect()
    {
        $this->connectionObj = new Connect($this->deviceParamsObject, $this->debug);

        return $this->connectionObj->connect();
    }

    private function loadTelnetLogin()
    {
        $this->loginObj = new Login($this->connectionObj);

        if (!$this->loginObj->login()) {
            (new SetDeviceStatus($this->connectionObj->device_id, 0))->setDeviceStatus();
            $logmsg = 'There was an authentication or connection issue with ' . $this->deviceParamsObject->deviceparams['device_name'];
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }
    }

    private function sendTelnetCommand()
    {
        $output = null;
        foreach ($this->connectionObj->commands as $command) {
            $output[$command] = $this->SendCommandObj->sendShowCommand($command);
        }

        return $output;
    }

    private function loadTelnetLogout()
    {
        $logout = new Logout($this->connectionObj);
        $logout->logout();
    }
}
