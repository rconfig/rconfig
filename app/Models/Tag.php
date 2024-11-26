<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends BaseModel
{
    use HasFactory;

    protected $searchableColumns = ['tagname'];
    protected $guarded = [];
    protected $appends = ['view_url'];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($tag) {

            if ($tag->devicesCount() > 0 || $tag->devicesCount() === 1) {
                throw new \Exception('Cannot delete tag set with related devices.');
            }
        });
    }

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/tags',
        );
    }

    public function devicesCount()
    {
        return $this->belongsToMany('App\Models\Device')->count();
    }

    public function Device()
    {
        return $this->belongsToMany('App\Models\Device')->where('status', '!=', 100);
    }
}
