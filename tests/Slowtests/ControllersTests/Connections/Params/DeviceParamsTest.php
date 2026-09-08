<?php

use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;

beforeEach(function () {
    $this->device = Device::where('id', 1001)->first();
});

test('returns formatted device params object', function () {
    $device_params = new DeviceParams($this->device->toArray());
    $result = $device_params->getAllDeviceParams();

    $this->assertStringContainsString($result->main['name'], 'Cisco IOS - TELNET - No Enable');
    $this->assertStringContainsString($result->deviceparams['device_name'], $this->device->device_name);
    $this->assertStringContainsString($result->deviceparams['device_ip'], $this->device->device_ip);
});
