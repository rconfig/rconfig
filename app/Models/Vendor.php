<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends BaseModel
{
    use HasFactory;

    protected $searchableColumns = ['vendorName'];

    protected $guarded = [];

    /**
     * Get the devices that owns the vendors.
     */
    public function device()
    {
        return $this->belongsToMany('App\Models\Device', 'foreign_key');
    }
}
