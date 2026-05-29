<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'task_email_notify' => 'boolean',
        'download_report_notify' => 'boolean',
        'verbose_download_report_notify' => 'boolean',
    ];

    protected $with = ['device', 'category', 'tag', 'finished'];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => '/scheduled-tasks',
        );
    }

    public function getTaskCronAttribute($value)
    {
        return explode(' ', trim($value));
    }

    public function tag(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Tag');
    }

    public function device(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Device');
    }

    public function category(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function finished()
    {
        return $this->hasOne('App\Models\MonitoredScheduledTasks');
    }
}
