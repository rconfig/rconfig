<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Taskdownloadreport extends Model
{
    use HasFactory;

    protected $dates = ['created_at', 'updated_at', 'start_time', 'end_time'];

    public function getCreatedAtAttribute($value)
    {
        $timezone = Config::get('app.timezone');

        return \Carbon\Carbon::createFromTimestamp(strtotime($value))
            // ->timezone($timezone)
            ->addHours($timezone)
            ->format('M d, Y G:iA'); // Feb 23, 2015 12:32 am
        // ->toDateTimeString();
    }
}
