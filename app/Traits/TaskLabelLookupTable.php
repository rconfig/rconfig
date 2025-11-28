<?php

namespace App\Traits;

// credit: https://chasingcode.dev/blog/simple-http-response-trait-laravel/

trait TaskLabelLookupTable
{
    protected function commandLookupTable($command)
    {
        $lookupTable = [
            'rconfig:download-device' => 'Devices - Config Downloads',
            'rconfig:download-category' => 'Categories - Config Downloads',
            'rconfig:download-tag' => 'Tags - Config Downloads',
            'rconfig:purge-configs' => 'Other Tasks - Purge Old Configs',
        ];

        return $lookupTable[$command];
    }
}
