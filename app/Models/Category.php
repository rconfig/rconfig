<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['view_url'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($cat) {
            if ($cat->devicesCount() > 0) {
                throw new \Exception('Cannot delete category with related devices.');
            }
            if ($cat->command()->count() > 0) {
                throw new \Exception('Cannot delete category with related commands.');
            }
        });
    }

    protected function viewUrl(): Attribute
    {
        // view url for search results
        return Attribute::make(
            get: fn() => '/categories',
        );
    }

    public function devicesCount()
    {
        return $this->belongsToMany('App\Models\Device', 'category_device', 'category_id', 'device_id')->count();
    }

    public function device()
    {
        return $this->belongsToMany('App\Models\Device');
    }

    public function command()
    {
        return $this->belongsToMany('App\Models\Command');
    }
}
