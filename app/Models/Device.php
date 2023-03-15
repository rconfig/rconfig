<?php

namespace App\Models;

use App\Casts\EncryptStringCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'device_password' => EncryptStringCast::class,
        'device_enable_password' => EncryptStringCast::class,
    ];

    public $fillable = [
        'device_name', 'device_ip', 'device_username', 'device_password', 'device_enable_password', 'device_cred_id', 'device_main_prompt', 'device_enable_prompt', 'device_category_id', 'device_template', 'device_model', 'device_version',
    ];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => '/device/view/' . $this->id,
        );
    }

    /**
     * Get the vendor record associated with the device.
     */
    public function vendor()
    {
        return $this->belongsToMany('App\Models\Vendor');
    }

    /**
     * Get the cat record associated with the device.
     */
    public function category()
    {
        return $this->belongsToMany('App\Models\Category');
    }

    /**
     * Get the tag record associated with the device.
     */
    public function tag()
    {
        return $this->belongsToMany('App\Models\Tag');
    }

    /**
     * Get the Template record associated with the device.
     */
    public function template()
    {
        return $this->belongsToMany('App\Models\Template');
    }

    public function lastConfig()
    {
        return $this->hasOne('App\Models\Config')->latestOfMany();
        // order by by how ever you need it ordered to get the latest
    }

    /**
     * Get the good config record associated with the device.
     */
    public function config_good()
    {
        return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '1');
    }

    /**
     * Get the bad config record associated with the device.
     */
    public function config_unknown()
    {
        return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '2');
    }

    /**
     * Get the bad config record associated with the device.
     */
    public function config_bad()
    {
        return $this->hasMany('App\Models\Config')->where('configs.download_status', '=', '0');
    }
}
