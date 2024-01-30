<?php

namespace App\Http\Controllers\Connections\Telnet;

class Send
{
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function sendString($command)
    {
        fwrite($this->connection, $command . "\r\n");
    }

    public function sendControlCode($code)
    {
        sleep(1);
        fwrite($this->connection, $code); // ASCII code for Ctrl+Y
    }
}
