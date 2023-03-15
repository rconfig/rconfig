<?php

namespace App\Http\Controllers\Connections\Telnet;

class Logout
{
    protected $connectionObj;

    protected $quit;

    public function __construct(object $connectionObj)
    {
        // dd($connectionObj);
        $this->connectionObj = $connectionObj;
        $this->quit = new Quit($this->connectionObj);
    }

    public function logout()
    {
        return $this->quit->closeTelnet();
    }
}
