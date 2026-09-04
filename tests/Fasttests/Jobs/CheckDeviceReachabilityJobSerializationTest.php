<?php

use App\Jobs\CheckDeviceReachabilityJob;
use App\Models\Device;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('serialized payload contains only device id no device attributes', function () {
    $device = Device::factory()->create([
        'device_username' => 'svc-admin-account',
        'device_ip' => '198.51.100.42',
    ]);

    $job = new CheckDeviceReachabilityJob($device->id);
    $serialized = serialize($job);

    // Left as PHPUnit-style assertions: Pest's toContain() has no message parameter,
    // so converting would silently drop the diagnostic text below.
    $this->assertStringNotContainsString(
        'svc-admin-account',
        $serialized,
        'Device username leaked into the serialized job payload.'
    );

    $this->assertStringNotContainsString(
        '198.51.100.42',
        $serialized,
        'Device IP leaked into the serialized job payload.'
    );

    $this->assertStringContainsString(
        (string) $device->id,
        $serialized,
        'Expected the device_id to be present in the serialized payload.'
    );
});
