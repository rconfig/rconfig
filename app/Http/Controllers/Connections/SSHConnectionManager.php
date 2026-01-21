<?php

namespace App\Http\Controllers\Connections;

use App\CustomClasses\SetDeviceStatus;
use App\Http\Controllers\Connections\SSH\Connect;
use App\Http\Controllers\Connections\SSH\Login;
use App\Http\Controllers\Connections\SSH\SendCommand;

class SSHConnectionManager
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

    public function SSHConnectionAndOutput()
    {
        if ($this->loadSSHConnect() === false) {
            return false;
        }
        if ($this->loadSSHLogin() === false) {
            (new SetDeviceStatus($this->connectionObj->device_id, 0))->setDeviceStatus();
            $logmsg = 'There was an authentication or connection issue with ' . $this->deviceParamsObject->deviceparams['device_name'];
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return ['failure' => $logmsg];
        }

        $outputArray = $this->sendSSHCommand();

        $this->setDebuggingOutput();
        $this->SSHDisconnect();

        return $outputArray;
    }

    private function loadSSHConnect()
    {
        $this->connectionObj = new Connect($this->deviceParamsObject, $this->debug);
        $this->connectionObj->connect();
    }

    private function loadSSHLogin()
    {
        try {
            $this->loginObj = new Login($this->connectionObj);
            $loginResult = $this->loginObj->login();
            if ($loginResult === false) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            if ($this->debug) {
                dump($e->getMessage());
            }
            $logmsg = $e->getMessage();
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }
    }

    private function setDebuggingOutput()
    {
        if ($this->connectionObj->cliDebugStatus === 1) {
            dump($this->connectionObj->connection->getlog());
        }
    }

    private function sendSSHCommand()
    {
        $output = null;
        $this->SendCommandObj = new SendCommand($this->connectionObj);
        foreach ($this->connectionObj->commands as $command) {
            $output[$command] = $this->SendCommandObj->sendShowCommand($command);
        }

        return $output;
    }

    private function SSHDisconnect()
    {
        // send resetPagingCommand if paging is set to on
        if ($this->connectionObj->paging === 'on') {
            $this->SendCommandObj->resetPagingCommand();
        }

        $this->connectionObj->connection->disconnect();
    }
}
