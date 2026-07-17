<?php

namespace Tests\Fasttests\Jobs;

use App\Jobs\CheckDeviceReachabilityJob;
use App\Models\Device;
use Tests\TestCase;

/**
 * Job-payload hygiene regression: CheckDeviceReachabilityJob must only ever
 * carry a device_id, never the full Device model, so nothing sensitive
 * (SSH/Telnet username, IP, encrypted password ciphertext) ends up in the
 * queue payload Horizon displays.
 */
class CheckDeviceReachabilityJobSerializationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_serialized_payload_contains_only_device_id_no_device_attributes(): void
    {
        $device = Device::factory()->create([
            'device_username' => 'svc-admin-account',
            'device_ip' => '198.51.100.42',
        ]);

        $job = new CheckDeviceReachabilityJob($device->id);
        $serialized = serialize($job);

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
    }
}
