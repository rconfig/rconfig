<?php

namespace Tests\Slowtests\ControllersTests\Connections\Params;

use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use Tests\TestCase;

class DeviceParamsTest extends TestCase
{
    protected $user;

    protected $device;

    public function setUp(): void
    {
        parent::setUp();
        $this->device = Device::where('id', 1001)->first();
    }

     public function test_returns_formatted_device_params_object()
    {
        $device_params = new DeviceParams($this->device->toArray());
        $result = $device_params->getAllDeviceParams();

        $this->assertStringContainsString($result->main['name'], 'Cisco IOS - TELNET - No Enable');
        $this->assertStringContainsString($result->deviceparams['device_name'], $this->device->device_name);
        $this->assertStringContainsString($result->deviceparams['device_ip'], $this->device->device_ip);
    }
}
