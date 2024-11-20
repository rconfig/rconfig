<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'id' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
