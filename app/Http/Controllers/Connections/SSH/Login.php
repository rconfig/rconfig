<?php

namespace App\Http\Controllers\Connections\SSH;

// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Send;
// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Read;
// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Quit;

use App\Models\DeviceCredentials;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;

class Login
{
    protected $send;
    protected $read;
    protected $connectionObj;
    protected $loadedKey;

    public function __construct(object $connectionObj)
    {
        $this->connectionObj = $connectionObj;
        // $this->send = new Send($this->connectionObj->connection);
        $this->send = new Send($this->connectionObj);
    }

    public function login()
    {
        $this->escapeTildeChars();

        if (! $this->authenticate()) {
            return false;
        }

        if ($this->shouldUseInteractiveLogin()) {
            $this->interactive_login();
        }

        $this->handleSplashScreen();

        if ($this->connectionObj->enable == 'on') {
            $this->enableModeLogin();
        } else {
            $this->sendPagingCommand();

            return true;
        }

        return true;
    }

    public function interactive_login()
    {
        $this->connectionObj->connection->read('~' . $this->connectionObj->usernamePrompt . '~', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->username . "\n");
        $this->connectionObj->connection->read('~' . $this->connectionObj->passwordPrompt . '~', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->password . "\n");
        return true;
    }

    private function authenticate(): bool
    {
        if ($this->connectionObj->sshPrivKey) {
            return $this->authenticateWithKey();
        }

        return $this->authenticateWithPassword();
    }

    private function authenticateWithPassword(): bool
    {
        $authOk = $this->loginErrorCheck();

        if ($authOk === true) {
            $this->HPChecks();
        }

        return $authOk;
    }

    private function authenticateWithKey(): bool
    {
        $cred = DeviceCredentials::where('id', $this->connectionObj->device_cred_id)->first();

        if (! $cred) {
            $logmsg = 'SSH Private key Device Credentials not found for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }

        try {
            $this->connectionObj->username = $cred->cred_username;
            $this->loadedKey = PublicKeyLoader::load($cred->ssh_key, $cred->ssh_key_passphrase ?? null);

            return $this->privKeyloginErrorCheck();
        } catch (\Exception $e) {
            $logmsg = 'Authentication Failed using SSH Private key for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Check key and passphrase.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');
            \Log::error($e);

            return false;
        }
    }

    private function shouldUseInteractiveLogin(): bool
    {
        return $this->connectionObj->sshInteractive === 'on' || $this->connectionObj->sshInteractive === 'yes';
    }

    private function handleSplashScreen(): void
    {
        // For RuggedCom and Avaya type devices that have splash screens
        if (! isset($this->connectionObj->hasSplashScreen) || $this->connectionObj->hasSplashScreen !== 'on') {
            return;
        }

        if (isset($this->connectionObj->hasSplashScreenEnterKey) && $this->connectionObj->hasSplashScreenEnterKey == 'on') {
            // some devices require an enter key to be sent to get past the splash screen
            $this->connectionObj->connection->write("\n");
            sleep(1);
        }

        $this->connectionObj->connection->read($this->connectionObj->splashScreenReadToText);
        $this->send->sendControlCode($this->connectionObj->splashScreenSendControlCode);
    }

    public function loginErrorCheck()
    {
        $loginSuccess = $this->connectionObj->connection->login($this->connectionObj->username, $this->connectionObj->password);

        if (! $loginSuccess) {
            $logmsg = 'Authentication Failed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            $logmsg = $this->appendLastError($logmsg);
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }

        return true;
    }

    public function privKeyloginErrorCheck()
    {
        $loginSuccess = $this->connectionObj->connection->login($this->connectionObj->username, $this->loadedKey);

        if (! $loginSuccess) {
            $logmsg = 'Authentication Failed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            $logmsg = $this->appendLastError($logmsg);
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }

        return true;
    }

    private function sendPagingCommand()
    {
        if ($this->connectionObj->paging === 'on') {
            // dd('~' . $this->connectionObj->devicePrompt . '~');
            $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
            $this->connectionObj->connection->write($this->connectionObj->pagingCmd . "\n");
            sleep(1);
            $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
        }
    }

    private function enableModeLogin()
    {
        $this->connectionObj->connection->write($this->connectionObj->enableCmd . "\n");

        if ($this->connectionObj->enableUsername === 'on') {
            $this->connectionObj->connection->read('~' . $this->connectionObj->enableUsernamePrmpt . '~', SSH2::READ_REGEX);
            $this->connectionObj->connection->write($this->connectionObj->username . "\n");
        }

        $this->connectionObj->connection->read('~' . $this->connectionObj->enablePassPrmpt . '~', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->enableModePassword . "\n");

        $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
        if ($this->connectionObj->paging === 'on') {
            $this->connectionObj->connection->write($this->connectionObj->pagingCmd . "\n");
            $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
        }
        $this->connectionObj->connection->write("\n"); // to line break after command output
        $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
    }

    private function HPChecks()
    {
        if ($this->connectionObj->hpAnyKeyStatus === 'on') {
            // $this->connectionObj->connection->read($this->connectionObj->hpAnyKeyPrmpt, SSH2::READ_REGEX);
            $this->connectionObj->connection->write("\n");
            $this->connectionObj->connection->write("\n");
            $this->connectionObj->connection->read('~' . $this->connectionObj->devicePrompt . '~', SSH2::READ_REGEX);
        }
    }

    private function escapeTildeChars()
    {

        // if the devicePrompt or enablePassPrmpt contains a tilde (~) character, it must be escaped with a backslash character. i.e. (\~)
        // zoho ticket #302 - devices with tilde in main prompt or enable prompt do not back up.
        // this is because we use tilde as the regex delimiter in the ssh2 library
        // so we need to escape the tilde character in the prompt with a backslash
        $this->connectionObj->pagingCmd = str_replace('~', '\~', $this->connectionObj->pagingCmd);
        $this->connectionObj->enableCmd = str_replace('~', '\~', $this->connectionObj->enableCmd);
        $this->connectionObj->enablePassPrmpt = str_replace('~', '\~', $this->connectionObj->enablePassPrmpt);
        $this->connectionObj->devicePrompt = str_replace('~', '\~', $this->connectionObj->devicePrompt);
    }

    private function appendLastError(string $logmsg): string
    {
        if (! method_exists($this->connectionObj->connection, 'getLastError')) {
            return $logmsg;
        }

        $lastError = trim((string) $this->connectionObj->connection->getLastError());
        if ($lastError === '') {
            return $logmsg;
        }

        return $logmsg . ' Last error: ' . $lastError;
    }
}
