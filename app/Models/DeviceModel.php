<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($deviceModel) {
            // Prevent deletion if the device model has associated devices
            if ($deviceModel->devices()->exists()) {
                throw new \Exception('Cannot delete device model that has associated devices. Please remove all devices first.');
            }
        });
    }


    public function devices()
    {
        return $this->hasMany(Device::class, 'device_model', 'name');
    }

    public function getDeviceCountAttribute()
    {
        return $this->devices()->count();
    }

    public function scopeSearch($query, $search)
    {
        if (! empty($search)) {
            return $query->where('name', 'like', '%' . $search . '%');
        }

        return $query;
    }

    public function scopeWithDevices($query)
    {
        return $query->has('devices');
    }

    public function scopeWithoutDevices($query)
    {
        return $query->doesntHave('devices');
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    public static function findByName($name)
    {
        return static::where('name', $name)->first();
    }

    public static function findByNameOrFail($name)
    {
        return static::where('name', $name)->firstOrFail();
    }

    public function getDeviceRelationshipKey()
    {
        return $this->name;
    }

    public function getDevicesCountFast()
    {
        return \App\Models\Device::where('device_model', $this->name)->count();
    }

    public function canBeDeleted()
    {
        return $this->getDevicesCountFast() === 0;
    }

    public function getDeviceNames()
    {
        return \App\Models\Device::where('device_model', $this->name)
            ->pluck('device_name');
    }

    public function scopeWithDeviceCount($query)
    {
        return $query->withCount([
            'devices' => function ($query) {
                $query->whereNotNull('device_model');
            },
        ]);
    }
}
