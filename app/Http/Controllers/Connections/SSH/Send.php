<?php

namespace App\Http\Controllers\Connections\SSH;

class Send
{
    protected $connectionObj;

    public function __construct($connectionObj)
    {
        $this->connectionObj = $connectionObj;
    }

    public function sendString($command)
    {
        return $this->connectionObj->connection->write($command."\r");
    }
}
