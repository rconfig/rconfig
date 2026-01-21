<?php

namespace App\Http\Controllers\Connections\Telnet;

use App\CustomClasses\SetDeviceStatus;
use App\Models\User;
use App\Notifications\DBDeviceConnectionFailureNotification;
use App\Enums\NotificationType;
use App\Traits\NotificationDispatcher;

class Connect
{
    use NotificationDispatcher;

    public $connection;

    /* MAIN */
    public $name;
    public $desc;

    /* CONNECT */
    public $timeout;
    public $protocol;
    public $port;

    /* AUTH */
    public $usernamePrompt;
    public $passwordPrompt;
    public $enable;
    public $enableCmd;
    public $enablePassPrmpt;
    public $hpAnyKeyStatus;
    public $hpAnyKeyPrmpt;
    public $sshPrivKey;

    /* CONFIG */
    public $linebreak;
    public $paging;
    public $pagingCmd;
    public $resetPagingCmd;
    public $pagerPrompt;
    public $pagerPromptCmd;
    public $saveConfig;
    public $exitCmd;

    /* DEVICEPARAMS */
    public $device_id;
    public $hostname;
    public $username;
    public $password;
    public $enableModePassword;
    public $devicePrompt;
    public $enablePrompt;
    public $cliDebugStatus;
    public $commands;
    public $command;


    /* OPTIONS */
    public $AnsiHost;
    public $setWindowSize;
    public $setTerminalDimensions;

    /* VT100 */
    public $hasSplashScreen;
    public $splashScreenReadToText;
    public $splashScreenSendControlCode;

    public function __construct(object $deviceParamsObject, $debug)
    {
        // dd($deviceParamsObject);
        /* MAIN */
        $this->name = $deviceParamsObject->main['name'];
        $this->desc = $deviceParamsObject->main['desc'];
        /* CONNECT */
        $this->timeout = $deviceParamsObject->connect['timeout'];
        $this->protocol = $deviceParamsObject->connect['protocol'];
        $this->port = $deviceParamsObject->deviceparams['device_port_override'] ?? $deviceParamsObject->connect['port'];
        /* AUTH */
        $this->usernamePrompt = $deviceParamsObject->auth['username'];
        $this->passwordPrompt = $deviceParamsObject->auth['password'];
        $this->enable = $deviceParamsObject->auth['enable'];
        $this->enableCmd = $deviceParamsObject->auth['enableCmd'];
        $this->enablePassPrmpt = $deviceParamsObject->auth['enablePassPrmpt'];
        $this->hpAnyKeyStatus = $deviceParamsObject->auth['hpAnyKeyStatus'];
        $this->hpAnyKeyPrmpt = $deviceParamsObject->auth['hpAnyKeyPrmpt'];
        // Optional SSHPrivKey Setting
        $this->sshPrivKey = isset($deviceParamsObject->auth['sshPrivKey']) ? $deviceParamsObject->auth['sshPrivKey'] : null;
        /* CONFIG */
        $this->linebreak = $deviceParamsObject->config['linebreak'];
        $this->paging = $deviceParamsObject->config['paging'];
        $this->pagingCmd = $deviceParamsObject->config['pagingCmd'];
        $this->resetPagingCmd = $deviceParamsObject->config['resetPagingCmd'];
        $this->pagerPrompt = $deviceParamsObject->config['pagerPrompt'];
        $this->pagerPromptCmd = $deviceParamsObject->config['pagerPromptCmd'];
        $this->saveConfig = $deviceParamsObject->config['saveConfig'];
        $this->exitCmd = $deviceParamsObject->config['exitCmd'];
        /* DEVICEPARAMS */
        $this->device_id = $deviceParamsObject->deviceparams['id'];
        $this->hostname = $deviceParamsObject->deviceparams['device_ip'];
        if (filter_var($this->hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            //setup ipv6 string for fsockopen
            $this->hostname = '[' . $this->hostname . ']';
        }
        $this->username = $deviceParamsObject->deviceparams['device_username'];
        $this->password = $deviceParamsObject->deviceparams['device_password'];
        $this->enableModePassword = $deviceParamsObject->deviceparams['device_enable_password'];
        $this->devicePrompt = $deviceParamsObject->deviceparams['device_main_prompt'];
        $this->enablePrompt = $deviceParamsObject->deviceparams['device_enable_prompt'];
        /* OPTIONS */
        $this->AnsiHost = isset($deviceParamsObject->options['AnsiHost']) ? $deviceParamsObject->options['AnsiHost'] : null; // ssh option only - keeping here for consistency
        $this->setWindowSize = isset($deviceParamsObject->options['setWindowSize']) ? $deviceParamsObject->options['setWindowSize'] : null; // ssh option only - keeping here for consistency
        $this->setTerminalDimensions = isset($deviceParamsObject->options['setTerminalDimensions']) ? $deviceParamsObject->options['setTerminalDimensions'] : null; // ssh option only - keeping here for consistency

        /* VT100 */
        $this->hasSplashScreen = isset($deviceParamsObject->vt100['hasSplashScreen']) ? $deviceParamsObject->vt100['hasSplashScreen'] : null;
        $this->splashScreenReadToText = isset($deviceParamsObject->vt100['splashScreenReadToText']) ? $deviceParamsObject->vt100['splashScreenReadToText'] : null;
        $this->splashScreenSendControlCode = isset($deviceParamsObject->vt100['splashScreenSendControlCode']) ? $deviceParamsObject->vt100['splashScreenSendControlCode'] : null;

        $this->cliDebugStatus = $debug;
        $this->commands = $deviceParamsObject->deviceparams['commands'];
    }

    public function connect()
    {
        $this->telnetPortValidOrDefault();
        $this->connection = @fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);
        if ($this->checkConnectionState() === false) {
            return false;
        }
        $this->setStreamTimeout();

        return $this->connection;
    }

    private function telnetPortValidOrDefault()
    {
        if ($this->port == null || $this->port <= 0 || $this->port > 65535) {
            return $this->port;
        } else {
            return $this->port;
        }
    }

    private function setStreamTimeout()
    {
        return stream_set_timeout($this->connection, $this->timeout);
    }

    private function checkConnectionState()
    {
        if (! $this->connection) {
            $logmsg = 'Unable to connect to ' . ($this->hostname . ' - ID:' . $this->device_id);

            $this->sendToDefaultChannels(
                NotificationType::CONNECTION_DEVICE_FAILURE,
                new DBDeviceConnectionFailureNotification($logmsg, $this->device_id)
            );

            (new SetDeviceStatus($this->device_id, 0))->setDeviceStatus();
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->hostname, $this->device_id, 'device');

            return false;
        }
    }
}
