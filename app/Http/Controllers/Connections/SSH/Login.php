<?php

namespace App\Http\Controllers\Connections\SSH;

// use App\Http\Controllers\Connections\SSH\Send;
// use App\Http\Controllers\Connections\SSH\Read;
// use App\Http\Controllers\Connections\SSH\Quit;
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

    // public function interactive_login()
    // {
    //     $this->connectionObj->connection->login($this->connectionObj->username);
    //     echo $this->connectionObj->connection->read('User:');
    //     echo $this->connectionObj->connection->write($this->connectionObj->username . "\n");
    //     echo $this->connectionObj->connection->read('Password:');
    //     echo $this->connectionObj->connection->write($this->connectionObj->password . "\n");
    //     echo $this->connectionObj->connection->read();
    //     echo $this->connectionObj->connection->write($this->connectionObj->pagingCmd . "\n");
    //     echo $this->connectionObj->connection->read($this->connectionObj->devicePrompt);
    //     return true;
    // }

    public function login()
    {
        if ($this->connectionObj->sshPrivKey) {
            // $privKeyRecord = PrivSshKeys::find($this->connectionObj->ssh_key_id);
            // $privateKey = file_get_contents($privKeyRecord->privSshKeyFile);

            // $this->loadedKey = PublicKeyLoader::load($privateKey);
            // $this->privKeyloginErrorCheck();
        } else {
            $this->loginErrorCheck();
        }

        if ($this->connectionObj->enable == 'on') {
            $this->enableModeLogin();
        } else {

            $this->sendPagingCommand();

            return true;
        }
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
        // valid prompt check
        $res = $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        // dd($res);
        if (!preg_match('/' . $this->connectionObj->devicePrompt . '/i', $res)) {
            // $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Prompt not did not match for device within timeout - This can cause slower config downloads. Device ID: ' . $this->connectionObj->device_id, 'connection', $this->connectionObj->hostname, $this->connectionObj->device_id, 'device');
        }

        $this->connectionObj->connection->write("\n");

        if ($this->connectionObj->paging === 'on') {

            $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);

            $this->connectionObj->connection->write($this->connectionObj->pagingCmd . "\n");
            sleep(1);
            $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        }
    }

    private function enableModeLogin()
    {
        $this->connectionObj->connection->write($this->connectionObj->enableCmd . "\n");
        $this->connectionObj->connection->read('/(' . $this->connectionObj->enablePassPrmpt . ')/i', SSH2::READ_REGEX);
        $this->connectionObj->connection->write($this->connectionObj->enableModePassword . "\n");
        $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        if ($this->connectionObj->paging === 'on') {
            $this->connectionObj->connection->write($this->connectionObj->pagingCmd . "\n");
        }
        $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        $this->connectionObj->connection->write("\n"); // to line break after command output
        $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
    }

    // private function HPChecks()
    // {
    //     if ($this->_hpAnyKeyStatus === true) {
    //         $this->connectionObj->connection->read($this->_hpAnyKeyPrmpt, SSH2::READ_REGEX);
    //         $this->connectionObj->connection->write("\n");
    //     }
    //     $this->connectionObj->connection->read($this->connectionObj->devicePrompt, SSH2::READ_REGEX);
    // }
}