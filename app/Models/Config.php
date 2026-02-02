<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use File;

class Config extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'download_status' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($config) {

            if (! is_int($config->download_status)) {
                $config->download_status = 0;
            }

            $summary = ConfigSummary::firstOrCreate(['device_id' => (int) $config->device_id]);
            $summary->increment('download_status_' . $config->download_status . '_count');
            $summary->increment('total_count');
            $summary->increment('total_file_count');
        });

        static::deleted(function ($config) {

            if (! is_int($config->download_status)) {
                $config->download_status = 0;
            }

            $summary = ConfigSummary::firstOrCreate(['device_id' => (int) $config->device_id]);

            // Prevent negative values when decrementing
            if ($summary->{'download_status_' . $config->download_status . '_count'} > 0) {
                $summary->decrement('download_status_' . $config->download_status . '_count');
            } else {
                // Reset to zero if it would go negative
                $summary->{'download_status_' . $config->download_status . '_count'} = 0;
                $summary->save();
            }

            // Same for total_count
            if ($summary->total_count > 0) {
                $summary->decrement('total_count');
            } else {
                $summary->total_count = 0;
                $summary->save();
            }

            // Decrement total_file_count when deleting (ADDED)
            if ($summary->total_file_count > 0) {
                $summary->decrement('total_file_count');
            } else {
                $summary->total_file_count = 0;
                $summary->save();
            }

            if ($config->config_location != null) {
                // if config_location is in tests/storage/ do not delete
                if (str_contains($config->config_location, 'tests/storage/')) {
                    return;
                }
                File::delete($config->config_location);
            }
        });

    }

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'id')->select('id', 'device_name', 'device_ip');
    }

    public function scopeCreatedAtBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
