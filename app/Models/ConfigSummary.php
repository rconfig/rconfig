<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'download_status_0_count',
        'download_status_1_count',
        'download_status_2_count',
        'total_count',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
