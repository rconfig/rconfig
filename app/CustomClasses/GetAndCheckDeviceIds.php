<?php

namespace App\CustomClasses;

use App\Models\Device;

class GetAndCheckDeviceIds
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function GetDeviceRecords()
    {
        return Device::with('category')->whereIn('id', $this->ids)->get();
    }
}
