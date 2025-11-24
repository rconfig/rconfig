<?php

namespace App\Models;

use App\Casts\EncryptStringCast;
use App\Models\DeviceComment;
use App\Models\DeviceCredentials;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends BaseModel
{
    use HasFactory;

    public const STATUS_UNREACHABLE = 0;

    public const STATUS_REACHABLE = 1;

    public const STATUS_UNKNOWN = 2;

    public const STATUS_DISABLED = 100;

    protected $searchableColumns = ['deviceName', 'deviceIpAddr'];

    protected $casts = [
        'device_password' => EncryptStringCast::class,
        'device_enable_password' => EncryptStringCast::class,
    ];

    protected $guarded = [];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/device/view/' . $this->id,
        );
    }

    public function vendor()
    {
        return $this->belongsToMany('App\Models\Vendor');
    }

    public function category()
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function tag()
    {
        return $this->belongsToMany('App\Models\Tag');
    }

    public function template()
    {
        return $this->belongsToMany('App\Models\Template');
    }

    public function deviceModel()
    {
        return $this->belongsTo(DeviceModels::class, 'device_model', 'name');
    }

    public function comments()
    {
        return $this->hasMany(DeviceComment::class)->where('is_open', 1);
    }

    public function deviceCred()
    {
        return $this->belongsTo(DeviceCredentials::class, 'device_cred_id', 'id');
    }

    public function deviceCredName()
    {
        return $this->deviceCred()->select(['id', 'cred_name']);
    }

    public function lastConfig()
    {
        return $this->hasOne('App\Models\Config')->latest();
        // order by by how ever you need it ordered to get the latest
    }

    public function configSummary()
    {
        return $this->hasOne(ConfigSummary::class);
    }

    // /**
    //  * Get the good config record associated with the device.
    //  */
    // public function config_good()
    // {
    //     return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '1');
    // }

    // /**
    //  * Get the bad config record associated with the device.
    //  */
    // public function config_unknown()
    // {
    //     return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '2');
    // }

    // /**
    //  * Get the bad config record associated with the device.
    //  */
    // public function config_bad()
    // {
    //     return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '0');
    // }
}
