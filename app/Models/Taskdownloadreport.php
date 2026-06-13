<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Taskdownloadreport extends Model
{
    use HasFactory;

    protected $dates = ['created_at', 'updated_at', 'start_time', 'end_time'];

    /**
     * Render the report's created_at in the application's configured timezone.
     *
     * The stored value is reinterpreted onto the configured timezone so the
     * download time shown in task reports matches local time rather than UTC.
     */
    public function getCreatedAtAttribute(?string $value): string
    {
        $timezone = Config::get('app.timezone');

        return Carbon::createFromTimestamp(strtotime((string) $value))
            ->setTimezone($timezone)
            ->format('M d, Y G:iA'); // Jun 13, 2026 2:00AM
    }
}
