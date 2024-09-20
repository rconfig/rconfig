<?php

namespace App\Http\Controllers\Connections\SSH;

// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Send;
// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Read;
// use App\Http\use App\Services\Connections\Params\DeviceParams;\SSH\Quit;
use App\Models\PrivSshKeys;
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
        // $this->read = new Read($this->connectionObj);
    }

    public function login()
    {

        $this->escapeTildeChars();

        if ($this->connectionObj->sshPrivKey) {
            // $privKeyRecord = PrivSshKeys::find($this->connectionObj->ssh_key_id);
            // $privateKey = file_get_contents($privKeyRecord->privSshKeyFile);

            // $this->loadedKey = PublicKeyLoader::load($privateKey);
            // $this->privKeyloginErrorCheck();
        } else {
            $this->loginErrorCheck();
            $this->HPChecks();
        }

        if ($this->connectionObj->sshInteractive === 'on') {
            $this->interactive_login();
        }

        if ($this->connectionObj->enable == 'on') {
            $this->enableModeLogin();
        } else {
            $this->sendPagingCommand();

            return true;
        }
    }

    public function interactive_login()
    {
        $this->connectionObj->connection->read('~' . $this->connectionObj->usernamePrompt . '~', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->username . "\n");
        $this->connectionObj->connection->read('~' . $this->connectionObj->passwordPrompt . '~', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->password . "\n");
        return true;
    }


    public function loginErrorCheck()
    {
        if (!$this->connectionObj->connection->login($this->connectionObj->username, $this->connectionObj->password)) {
            $logmsg = 'Authentication Failed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }
    }

    public function privKeyloginErrorCheck()
    {
        if (!$this->connectionObj->connection->login($this->connectionObj->username, $this->loadedKey)) {
            $logmsg = 'Authentication Failed for ' . ($this->connectionObj->hostname . ' ID:' . $this->connectionObj->device_id) . '. Or wrong prompt configured for this device! Check your device settings.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $logmsg, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');

            return false;
        }
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
}
