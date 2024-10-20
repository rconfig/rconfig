<?php

namespace App\Models;

use App\Casts\EncryptStringCast;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCredentials extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'cred_password' => EncryptStringCast::class,
        'cred_enable_password' => EncryptStringCast::class,

    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($cred) {

            if ($cred->devicesCount() > 0) {
                throw new \Exception('Cannot delete credential set with related devices.');
            }
        });
    }

    public function devicesCount()
    {
        return Device::where('device_cred_id', $this->id)->count();
    }

    public function device()
    {
        return $this->hasMany(Device::class, 'device_cred_id');
    }
}
