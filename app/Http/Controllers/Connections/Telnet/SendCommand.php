<?php

namespace App\Http\Controllers\Connections\Telnet;

use Illuminate\Support\Str;

class SendCommand extends Read
{
    protected $send;
    protected $read;
    protected $connectionObj;

    public function __construct(object $connectionObj)
    {
        $this->connectionObj = $connectionObj;
        $this->send = new Send($this->connectionObj->connection);
        $this->read = new Read($this->connectionObj);
    }

    public function sendShowCommand($command)
    {
        // command contains var replace it
        $command = $this->replaceVariables($command);

        $this->send->sendString($command);
        if ($this->read->readTo($this->connectionObj->devicePrompt) == true) {
            // $this->read->data is inherited by Read class extentsion/ inheritence
            $this->read->data = $this->explodeTextToArray();
            $this->dropFirstAndLastLinesFromArray();
            $result = $this->createArrayFromData();
            $this->read->data = ''; // reset after converting the data object to array from the read class - just above!

            return $result;
        }
    }

    private function explodeTextToArray()
    {
        return explode("\r\n", $this->read->data);
    }

    /**
     * Strip the echoed command from the front and the trailing prompt from the back.
     *
     * Both are positional guesses, so they are only safe when at least one line of
     * real output survives. With fewer than three lines the strip would consume the
     * entire result and persist an empty config as a successful backup, so leave the
     * output untouched instead.
     */
    private function dropFirstAndLastLinesFromArray(): void
    {
        if (count($this->read->data) < 3) {
            return;
        }

        array_shift($this->read->data); // drops the command that was run from the output
        array_pop($this->read->data); // removes last line, usually a prompt
    }

    private function createArrayFromData()
    {
        $result = [];
        if (count($this->read->data) > 0) {
            foreach ($this->read->data as $line) {
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
