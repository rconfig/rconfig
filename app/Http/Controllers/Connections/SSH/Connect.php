<?php

namespace App\Http\Controllers\Connections\SSH;

use App\CustomClasses\SetDeviceStatus;
use App\Enums\NotificationType;
use App\Notifications\DBDeviceConnectionFailureNotification;
use App\Traits\NotificationDispatcher;
use phpseclib4\Net\SSH2;

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
    public $kexOverride;
    public $kexAlgorithms;

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
    public $commandLinebreak;
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
        $this->kexOverride = $deviceParamsObject->connect['kexOverride'] ?? null;
        $this->kexAlgorithms = $deviceParamsObject->connect['kexAlgorithms'] ?? null;
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
        $this->commandLinebreak = $this->resolveCommandLinebreak($deviceParamsObject->config['commandLinebreak'] ?? null);
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
        $this->timeout = $this->sshTimeoutValidOrNone();
        $this->connection = new SSH2($this->hostname, $this->port, $this->timeout);
        $this->applyKexAlgorithmOverride();
        if (is_array($this->setWindowSize) && count($this->setWindowSize) === 2) {
            $this->connection->setWindowSize((int) $this->setWindowSize[0], (int) $this->setWindowSize[1]);
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

    /**
     * phpseclib 4 types the timeout as int, so a template timeout that is null, empty
     * or not a number would throw a TypeError before the device is dialled. phpseclib
     * 3 treated a missing timeout as no timeout, which phpseclib 4 spells as 0, so
     * anything unusable maps to 0 and keeps that behaviour. A fractional timeout rounds
     * up so it never collapses to 0.
     */
    private function sshTimeoutValidOrNone(): int
    {
        if (! is_numeric($this->timeout) || $this->timeout < 0) {
            return 0;
        }

        return (int) ceil((float) $this->timeout);
    }

    /**
     * Restricts key exchange to the template's algorithm list, for vendor sshd builds
     * that hang negotiating phpseclib's default preferences (e.g. curve25519) but
     * complete the handshake against a specific classic Diffie-Hellman group. Off by
     * default: only applies when the template opts in via connect.kexOverride.
     * Ported from Pro RCO-1191, see RCO-1588.
     */
    private function applyKexAlgorithmOverride(): void
    {
        if (! $this->kexOverrideEnabled() || empty($this->kexAlgorithms) || ! is_array($this->kexAlgorithms)) {
            return;
        }

        $this->connection->setPreferredAlgorithms(['kex' => array_values($this->kexAlgorithms)]);
    }

    /**
     * Matches Pro's tolerant reading of connect.kexOverride: 'on', 'yes', 'true' and '1'
     * in any case, plus the YAML boolean true and the integer 1, all mean enabled.
     */
    private function kexOverrideEnabled(): bool
    {
        $value = $this->kexOverride;

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (! is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['on', 'yes', 'true', '1'], true);
    }

    /**
     * Resolves the line ending appended to every command written to the interactive shell.
     * Opt-in via config.commandLinebreak ('r', 'n' or 'rn') for devices such as Force10/FTOS
     * that ignore a bare carriage return. Absent or unrecognised values keep the historical
     * "\r", so existing templates behave exactly as before. Ported from Pro RCO-1546, see RCO-1588.
     */
    private function resolveCommandLinebreak(mixed $value): string
    {
        $lineEndings = ['r' => "\r", 'n' => "\n", 'rn' => "\r\n"];

        if (! is_string($value)) {
            return $lineEndings['r'];
        }

        return $lineEndings[strtolower(trim($value))] ?? $lineEndings['r'];
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
