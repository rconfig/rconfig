<?php

namespace App\Http\Controllers\Connections;

use App\Http\Controllers\Connections\Params\DeviceParams;

class MainConnectionManager
{
    protected $deviceRecord;

    protected $deviceParamsObject;

    protected $telnetConnection;

    protected $sshConnection;

    protected $debug;

    public function __construct(array $deviceRecord, $debug)
    {
        $this->deviceRecord = $deviceRecord;
        $this->debug = $debug;
    }

    public function setupConnectAndReturnOutput()
    {
        $this->getAllConnectionParamsArray();

        if ($this->deviceParamsObject->connect['protocol'] == 'telnet') {
            $this->telnetConnection = new TelnetConnectionManager($this->deviceParamsObject, $this->debug);

            return $this->telnetConnection->telnetConnectionAndOutput();
        } elseif ($this->deviceParamsObject->connect['protocol'] == 'ssh') {
            $this->sshConnection = new SSHConnectionManager($this->deviceParamsObject, $this->debug);

            return $this->sshConnection->SshConnectionAndOutput();
        } else {
            throw new \Exception('Error Processing '.__CLASS__.' - '.__FUNCTION__.' Request. Your rConfig template file could be invalid.', 1);
        }
    }

    public function getAllConnectionParamsArray()
    {
        $deviceParams = new DeviceParams($this->deviceRecord);
        $this->deviceParamsObject = $deviceParams->getAllDeviceParams();

        return $this->deviceParamsObject;
    }
}
