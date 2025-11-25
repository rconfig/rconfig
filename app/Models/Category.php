<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['view_url'];

    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => '/commandgroups',
        );
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($cat) {
            if ($cat->device()->count() > 0) {
                throw new \Exception('Cannot delete command group with related devices.');
            }
            if ($cat->command()->count() > 0) {
                throw new \Exception('Cannot delete command group with related commands.');
            }
        });
    }

    public function devicesCount()
    {
        return $this->belongsToMany('App\Models\Device', 'category_device', 'category_id', 'device_id')->count();
    }

    public function device()
    {
        return $this->belongsToMany('App\Models\Device')->where('status', '!=', 100);
    }

    public function command()
    {
        return $this->belongsToMany('App\Models\Command');
    }
}
