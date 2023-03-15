<?php

namespace App\CustomClasses;

use App\Models\Setting;

class Debug
{
    private $_debugState;

    public function __construct($debugState = false)
    {
        $this->_debugState = $debugState;
    }

    public function debugDump($message)
    {
        if (\Request::segment(1) == 'web') {
            return;
        } // no debug on web invokation

        $debugging = Setting::find(1)->value('deviceDebugging');
        if ($this->debugcheck() == 1 || $this->_debugState == true) {
            dump($message);
        }
    }

    public function debugcheck()
    {
        return Setting::find(1)->value('deviceDebugging');
    }
}
