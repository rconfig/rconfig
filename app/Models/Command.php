<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class Command extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/commands',
        );
    }

    public function Category()
    {
        return $this->belongsToMany('App\Models\Category');
    }
}
