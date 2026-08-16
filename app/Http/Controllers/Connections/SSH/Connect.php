<?php

namespace App\Http\Controllers\Connections\SSH;

use App\CustomClasses\SetDeviceStatus;
use App\Enums\NotificationType;
use App\Notifications\DBDeviceConnectionFailureNotification;
use App\Traits\NotificationDispatcher;
use phpseclib3\Net\SSH2;

class Connect
{
    use NotificationDispatcher;

    /**
     * Port dialled when the template or the device override does not carry a usable one.
     */
    private const DEFAULT_SSH_PORT = 22;

    public $connection;

    /* MAIN */
    public $name;
    public $desc;

    /* CONNECT */
    public $timeout;
    public $protocol;
    public $port;
    public $isNonInteractiveMode;

    /* AUTH */
    public $sshInteractive;
    public $usernamePrompt;
    public $passwordPrompt;
    public $enable;
    public $enableCmd;
    public $enableUsername;
    public $enableUsernamePrmpt;
    public $enablePassPrmpt;
    public $hpAnyKeyStatus;
    public $device_cred_id;
    public $sshPrivKey;
    public $ssh_key_id;

    /* CONFIG */
    public $paging;
    public $pagingCmd;
    public $resetPagingCmd;
    public $saveConfig;
    public $exitCmd;
    public $isMikrotik;

    /* DEVICEPARAMS */
    public $device_id;
    public $hostname;
    public $username;
    public $password;
    public $devicePrompt;
    public $enablePrompt;
    public $enableModePassword;
    public $cliDebugStatus;
    public $command;
    public $commands;
    public $snippet;

    /* OPTIONS */
    public $AnsiHost;
    public $setWindowSize;
    public $setTerminalDimensions;

    /* VT100 */
    public $hasSplashScreen;
    public $hasSplashScreenEnterKey;
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
        $this->isNonInteractiveMode = $deviceParamsObject->connect['isNonInteractiveMode'] ?? null;
        /* AUTH */
        $this->sshInteractive = $deviceParamsObject->auth['sshInteractive'] ?? null;
        $this->usernamePrompt = $deviceParamsObject->auth['username'];
        $this->passwordPrompt = $deviceParamsObject->auth['password'];
        $this->enable = $deviceParamsObject->auth['enable'];
        $this->enableCmd = $deviceParamsObject->auth['enableCmd'];
        $this->enableUsername = $deviceParamsObject->auth['enableUsername'] ?? null;
        $this->enableUsernamePrmpt = $deviceParamsObject->auth['enableUsernamePrmpt'] ?? null;
        $this->enablePassPrmpt = $deviceParamsObject->auth['enablePassPrmpt'];
        $this->hpAnyKeyStatus = $deviceParamsObject->auth['hpAnyKeyStatus'];
        // auth['hpAnyKeyPrmpt'] is deprecated and deliberately not read, see HPChecks in Login
        // Send in Cred ID for Optional SSHPrivKey Setting
        $this->device_cred_id = $deviceParamsObject->deviceparams['device_cred_id'] ?? null;
        // Optional SSHPrivKey Setting
        $this->sshPrivKey = isset($deviceParamsObject->auth['sshPrivKey']) ? $deviceParamsObject->auth['sshPrivKey'] : null;
        /* CONFIG */
        // config['linebreak'], config['pagerPrompt'] and config['pagerPromptCmd'] are
        // deprecated. Nothing reads them, so they are deliberately not loaded here.
        $this->paging = $deviceParamsObject->config['paging'];
        $this->pagingCmd = $deviceParamsObject->config['pagingCmd'];
        $this->resetPagingCmd = $deviceParamsObject->config['resetPagingCmd'];
        $this->saveConfig = $deviceParamsObject->config['saveConfig'];
        $this->exitCmd = $deviceParamsObject->config['exitCmd'];
        /* DEVICEPARAMS */
        $this->device_id = $deviceParamsObject->deviceparams['id'];
        $this->hostname = $deviceParamsObject->deviceparams['device_ip'];
        if (filter_var($this->hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // setup ipv6 string for fsockopen
            $this->hostname = '[' . $this->hostname . ']';
        }
        $this->username = $deviceParamsObject->deviceparams['device_username'];
        $this->password = $deviceParamsObject->deviceparams['device_password'];
        $this->enableModePassword = $deviceParamsObject->deviceparams['device_enable_password'];
        $this->ssh_key_id = $deviceParamsObject->deviceparams['ssh_key_id'];
        $this->devicePrompt = $deviceParamsObject->deviceparams['device_main_prompt'];
        $this->enablePrompt = $deviceParamsObject->deviceparams['device_enable_prompt'];
        $this->isMikrotik = isset($deviceParamsObject->config['isMikrotik']) ? $deviceParamsObject->config['isMikrotik'] : null;

        /* OPTIONS */
        $this->AnsiHost = isset($deviceParamsObject->options['AnsiHost']) ? $deviceParamsObject->options['AnsiHost'] : null;
        $this->setWindowSize = isset($deviceParamsObject->options['setWindowSize']) ? $deviceParamsObject->options['setWindowSize'] : null;
        // Screen size for ANSI output rendering only, it does not change the negotiated
        // terminal. See https://api.phpseclib.org/2.0/File_ANSI.html#method_setDimensions
        $this->setTerminalDimensions = isset($deviceParamsObject->options['setTerminalDimensions']) ? $deviceParamsObject->options['setTerminalDimensions'] : null;

        /* VT100 */
        $this->hasSplashScreen = isset($deviceParamsObject->vt100['hasSplashScreen']) ? $deviceParamsObject->vt100['hasSplashScreen'] : null;
        $this->hasSplashScreenEnterKey = isset($deviceParamsObject->vt100['hasSplashScreenEnterKey']) ? $deviceParamsObject->vt100['hasSplashScreenEnterKey'] : null;
        $this->splashScreenReadToText = isset($deviceParamsObject->vt100['splashScreenReadToText']) ? $deviceParamsObject->vt100['splashScreenReadToText'] : null;
        $this->splashScreenSendControlCode = isset($deviceParamsObject->vt100['splashScreenSendControlCode']) ? $deviceParamsObject->vt100['splashScreenSendControlCode'] : null;

        $cliDebugStatus = ($debug === true) ? 1 : 0; // convert debug to boolean
        $this->cliDebugStatus = $cliDebugStatus;
        $this->commands = $deviceParamsObject->deviceparams['commands'];
        if (isset($deviceParamsObject->deviceparams['snippet'])) {
            $this->snippet = $deviceParamsObject->deviceparams['snippet'];
        }
    }

    public function connect()
    {
        $this->port = $this->sshPortValidOrDefault();
        $this->connection = new SSH2($this->hostname, $this->port, $this->timeout);
        if ($this->setWindowSize != null) {
            $this->connection->setWindowSize($this->setWindowSize[0], $this->setWindowSize[1]);
        }
        $this->checkConnectionState();
        $this->SSHdebuggingCheck();

        return $this->connection;
    }

    /**
     * A template or a device port override can carry a null, empty or out of range
     * port, which would otherwise be dialled as written. Fall back to the SSH default.
     */
    private function sshPortValidOrDefault(): int
    {
        $port = (int) $this->port;

        if ($port > 0 && $port <= 65535) {
            return $port;
        }

        return self::DEFAULT_SSH_PORT;
    }

    private function SSHdebuggingCheck()
    {
        // debugging check - real time output on CLI
        if ($this->cliDebugStatus && ! defined('NET_SSH2_LOGGING')) {
            define('NET_SSH2_LOGGING', SSH2::LOG_COMPLEX);
        }
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
