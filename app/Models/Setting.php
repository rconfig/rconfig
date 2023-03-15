<?php

namespace App\Models;

use App\Casts\EncryptStringCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    public $table = 'settings';

    protected $guarded = [];

    protected $casts = [
        'defaultDevicePassword' => EncryptStringCast::class,
        'defaultEnablePassword' => EncryptStringCast::class,
        'mail_password' => EncryptStringCast::class,
    ];
}
