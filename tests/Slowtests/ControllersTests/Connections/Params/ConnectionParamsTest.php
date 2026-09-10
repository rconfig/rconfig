<?php

use App\Http\Controllers\Connections\Params\ConnectionParams;
use App\Models\Device;

beforeEach(function () {
    $this->device = Device::where('id', 1001)->first();
});

test('can get a template for this device', function () {
    $template = new ConnectionParams($this->device->device_template);
    $result = $template->getTemplateParams();

    $this->assertStringContainsString($result['main']['name'], 'Cisco IOS - TELNET - No Enable');
    $this->assertStringContainsString($result['connect']['protocol'], 'telnet');
});
