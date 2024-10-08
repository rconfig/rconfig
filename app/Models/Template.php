<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/templates',
        );
    }

    public function Device()
    {
        return $this->belongsToMany('App\Models\Device');
    }
}
