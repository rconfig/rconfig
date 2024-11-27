<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends BaseModel
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['view_url'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($template) {

            if ($template->devicesCount() > 0) {
                throw new \Exception('Cannot delete template with related devices.');
            }
        });
    }

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/templates',
        );
    }

    public function devicesCount()
    {
        return $this->belongsToMany('App\Models\Device', 'device_template', 'template_id', 'device_id')->count();
    }

    public function Device()
    {
        return $this->belongsToMany('App\Models\Device')->where('status', '!=', 100);
    }
}
