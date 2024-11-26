<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends BaseModel
{
    use HasFactory;

    protected $searchableColumns = ['vendorName'];
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($vendor) {
            if ($vendor->devicesCount() > 0) {
                throw new \Exception('Cannot delete vendor with related devices.');
            }
        });
    }

    public function devicesCount()
    {
        return $this->belongsToMany('App\Models\Device', 'device_vendor', 'vendor_id', 'device_id')->count();
    }

    public function device()
    {
        return $this->belongsToMany('App\Models\Device')->where('status', '!=', 100);
    }
}
