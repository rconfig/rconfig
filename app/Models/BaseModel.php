<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /* Common properties, accessors and mutators here */

    public function getCreatedAtAttribute($value)
    {
        $timezone = \Config::get('app.timezone');
        if ($value === null) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = \Config::get('app.timezone');
        if ($value === null) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }

    public function getLastSeenAttribute($value)
    {
        $timezone = \Config::get('app.timezone');
        if ($value === null) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }

    public function getLastStartedAtAttribute($value)
    {
        $timezone = \Config::get('app.timezone');
        if ($value === null) {
            return null;
        }

        return \Carbon\Carbon::createFromTimestamp(strtotime($value))
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }
}
