<?php

use App\Models\Device;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('sys info returns correct structure', function () {
    $response = $this->json('get', '/api/dashboard/sysinfo');

    $response->assertStatus(200);
    $expectedStructure = [
        'OSVersion',
        'localIp',
        'PublicIP',
        'ServerName',
        'PHPVersion',
        'RedisVersion',
        'timezone',
        'url',
        'systemUptime',
    ];

    $dbDriver = config('database.default');
    if (in_array($dbDriver, ['mysql', 'test_mysql']) || strpos($dbDriver, 'pgsql') !== false) {
        $expectedStructure[] = 'MySQLVersion';
    }

    $response->assertJsonStructure($expectedStructure);
});

test('config info test', function () {
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
});

test('queue info test', function () {
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
});

test('config info returns device counts', function () {
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

    expect($response->json('data.deviceCount'))->toBeGreaterThanOrEqual(8);
    expect($response->json('data.deviceDownCount'))->toBeGreaterThanOrEqual(3);
});

afterEach(function () {
    $this->rollBackTransaction();
});
