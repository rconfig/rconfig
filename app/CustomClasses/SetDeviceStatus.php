<?php

namespace App\CustomClasses;

use App\Models\Device;
use Carbon\Carbon;

class SetDeviceStatus
{
    protected $status;

    protected $deviceid;

    public function __construct($deviceid, $status)
    {
        $this->status = $status;
        $this->deviceid = $deviceid;
    }

    public function setDeviceStatus()
    {
        $device = Device::findOrFail($this->deviceid);
        $device->last_seen = Carbon::now()->toDateTimeString();
        $device->status = $this->status;
        $device->save();
    }
}
