<?php

namespace App\Services\Utilities;

use Carbon\Carbon;

class ServerTimeService
{
    public function getServerTime()
    {
        // this format: December 17, 1995 03:24:00
        return Carbon::now()->format('F j, Y H:i:s');
    }
}
