<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    /* Common properties, accessors and mutators here */

    public function getCreatedAtAttribute($value)
    {
        $timezone = Config::get('app.timezone');

        return Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = Config::get('app.timezone');

        return Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }
}
