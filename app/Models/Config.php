<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Config extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'id')->select('id', 'device_name');
    }
}
