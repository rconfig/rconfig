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

        // TemplateNormaliser has already lowercased and trimmed this, so a compare
        // against the lowercase literals is all that is needed here.
        $protocol = $this->deviceParamsObject->connect['protocol'] ?? null;

        if ($protocol === 'telnet') {
            $this->telnetConnection = new TelnetConnectionManager($this->deviceParamsObject, $this->debug);

            return $this->telnetConnection->telnetConnectionAndOutput();
        } elseif ($protocol === 'ssh') {
            $this->sshConnection = new SSHConnectionManager($this->deviceParamsObject, $this->debug);

            return $this->sshConnection->SshConnectionAndOutput();
        } else {
            throw new \Exception('Error Processing ' . __CLASS__ . ' - ' . __FUNCTION__ . ' Request. Your rConfig template file carries connect.protocol "' . (is_scalar($protocol) ? $protocol : gettype($protocol)) . '", which is not one of ssh or telnet.', 1);
        }
    }

    public function getAllConnectionParamsArray()
    {
        $deviceParams = new DeviceParams($this->deviceRecord);
        $this->deviceParamsObject = $deviceParams->getAllDeviceParams();

        return $this->deviceParamsObject;
    }
}
