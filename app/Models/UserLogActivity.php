<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogActivity extends Model
{
    use HasFactory;

    protected $guarded = [];

    // with user
    protected $with = ['user'];

    // casts
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    // user relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
