<?php

namespace App\Http\Controllers\Connections\SSH;

use Illuminate\Support\Str;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SSH2;

class SendCommand
{
    protected $send;

    protected $read;

    protected $connectionObj;

    protected $data;

    public function __construct(object $connectionObj)
    {
        $this->connectionObj = $connectionObj;
        $this->send = new Send($this->connectionObj);
    }

    public function sendShowCommand($command)
    {
        if ($this->connectionObj->sshPrivKey) {
            return $this->connectionObj->connection->exec($command);
        } else {
            if ($this->connectionObj->AnsiHost === 'yes') {
                return $this->ansiShowCommand($command);
            }

            return $this->standardShowCommand($command);
        }
    }

    private function ansiShowCommand($command)
    {
        // example proper ansi workflow here: https://stackoverflow.com/questions/36078846/phpseclib-read-takes-previous-command-output/36079336#36079336
        $ansi = new ANSI();
        if (isset($this->connectionObj->connection->setTerminalDimensions)) {
            $ansi->setDimensions($this->connectionObj->connection->setTerminalDimensions[0], $this->connectionObj->connection->setTerminalDimensions[1]);
        }
        $ansi->setHistory(100000);

        $output = $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        $command = $this->replaceVariables($command);
        $this->send->sendString($command);
        $output = $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        $ansi->appendString($output);

        // echo $ansi->getScreen(); // outputs current screen HTML
        // echo $ansi->getHistory(); // outputs current history HTML
        // $this->data = htmlspecialchars_decode(strip_tags($ansi->getScreen())); //$ansi->getScreen() returns what'd be seen on the current screen. In the case of top this is desirable
        $this->data = htmlspecialchars_decode(strip_tags($ansi->getHistory())); // getHistory() used to retired all output see ANSi examples in http://phpseclib.sourceforge.net/ssh/2.0/examples.html
        if ($this->data) {
            $this->data = $this->explodeTextToArray();
            $this->dropFirstAndLastLinesFromArray();
            $result = $this->createArrayFromData();

            return $result;
        }
    }

    private function standardShowCommand($command)
    {
        // command contains var replace it
        $command = $this->replaceVariables($command);

        $this->send->sendString($command);
        // check if this is a HP device
        if ($this->connectionObj->hpAnyKeyStatus === 'on') {
            // hack to get around procurve VT100 special characters
            // https://stackoverflow.com/questions/9913342/byte-to-character-conversion-for-a-telnet-stream
            // https://stackoverflow.com/questions/1176904/php-how-to-remove-all-non-printable-characters-in-a-string
            $this->data = $this->connectionObj->connection->read();
            $this->data = preg_replace('/[^[:print:]\r\n]/', '', $this->data);
            $this->data = preg_replace('/\[24;0HE/', '', $this->data);
            $this->data = preg_replace('/\[24;38H/', '', $this->data);
            $this->data = preg_replace('/\[24;19H/', '', $this->data);
            $this->data = preg_replace('/\[?25h/', '', $this->data);
            $this->data = preg_replace('/\[1;24r/', '', $this->data);
            $this->data = preg_replace('/\[24;1H/', '', $this->data);
            $this->data = preg_replace('/\[2K/', '', $this->data);
        } else {
            $this->data = $this->connectionObj->connection->read('/(' . $this->connectionObj->devicePrompt . ')/i', SSH2::READ_REGEX);
        }
        if ($this->data) {
            $this->data = $this->explodeTextToArray();
            $this->dropFirstAndLastLinesFromArray();
            $result = $this->createArrayFromData();

            return $result;
        }
    }

    public function explodeTextToArray()
    {
        return explode("\r\n", $this->data);
    }

    public function dropFirstAndLastLinesFromArray()
    {
        array_shift($this->data); //drops the command that was run from the output
        array_pop($this->data); // removes last line, usually a prompt
    }

    public function createArrayFromData()
    {
        $result = [];
        if (count($this->data) > 0) {
            foreach ($this->data as $line) {
                $line = explode("\r\n", $line);
                array_push($result, $line[0]);
            }
        }

        return $result;
    }

    private function replaceVariables($command)
    {
        if (Str::contains($command, '{deviceid}')) {
            $command = str_replace('{deviceid}', $this->connectionObj->device_id, $command);
        }

        return $command;
    }
}
