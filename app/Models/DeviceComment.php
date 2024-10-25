<?php

namespace App\Models;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceComment extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'user_id', 'comment', 'is_open'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
