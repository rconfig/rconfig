<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => '/categories',
        );
    }

    /**
     * Category belongs to many devices
     */
    public function device()
    {
        return $this->belongsToMany('App\Models\Device');
    }

    /**
     * Category belongs to many commands
     */
    public function command()
    {
        return $this->belongsToMany('App\Models\Command');
    }
}
