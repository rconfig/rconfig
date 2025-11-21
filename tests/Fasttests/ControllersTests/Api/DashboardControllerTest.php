<?php

namespace Tests\Fasttests\ControllersTests\Api;
use App\Models\Device;

use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    /** @var \App\Models\User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_sys_info_returns_correct_structure()
    {
        $response = $this->json('get', '/api/dashboard/sysinfo');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'OSVersion',
            'localIp',
            'PublicIP',
            'ServerName',
            'PHPVersion',
            'RedisVersion',
            'MySQLVersion',
            'timezone',
            'url',
            'systemUptime',
        ]);
    }

    public function test_config_info_test()
    {
        $response = $this->json('get', '/api/dashboard/configinfo');
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'success',
                'data' => [
                    'deviceCount',
                    'deviceDownCount',
                    'configFileTotalCount',
                    'configTotalCount',
                    'failedConfigCount',
                    'lastConfig',
                ],
                'message',
            ]
        );
    }

    public function test_queue_info_test()
    {
        $response = $this->json('get', '/api/dashboard/queueinfo');

        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                'success',
                'data' => [
                    'q_total_count',
                    'q_failed_count',
                    'q_last_job',
                ],
                'message',
            ]
        );
    }

    public function test_config_info_returns_device_counts()
    {
        Device::factory()->count(5)->create(['status' => Device::STATUS_UNKNOWN]);
        Device::factory()->count(3)->create(['status' => Device::STATUS_UNREACHABLE]);

        $response = $this->json('get', '/api/dashboard/configinfo');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'deviceCount',
                'deviceDownCount',
                'configFileTotalCount',
                'configTotalCount',
                'failedConfigCount',
                'lastConfig',
            ],
            'message',
        ]);

        $this->assertGreaterThanOrEqual(8, $response->json('data.deviceCount'));
        $this->assertGreaterThanOrEqual(3, $response->json('data.deviceDownCount'));
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
