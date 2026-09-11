<?php

use App\Models\Config;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('purge failed config', function () {
    // Capture any pre-existing configs for this device so counts stay correct
    // regardless of data already present for device 1001.
    $initialTotal = Config::where('device_id', 1001)->count();
    $initialNonFailed = Config::where('device_id', 1001)->where('download_status', '!=', 0)->count();

    $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 0]);
    $failedConfigsCnt = $configs->where('download_status', 0)->where('device_id', 1001)->count();
    expect($failedConfigsCnt)->toEqual(50);

    $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 1]);
    $goodConfigsCnt = $configs->where('download_status', 1)->where('device_id', 1001)->count();
    expect($goodConfigsCnt)->toEqual(50);

    $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 2]);
    $unkownConfigsCnt = $configs->where('download_status', 2)->where('device_id', 1001)->count();
    expect($unkownConfigsCnt)->toEqual(50);

    $totalConfigsCount = Config::where('device_id', 1001)->count();
    expect($totalConfigsCount)->toEqual($initialTotal + 150);

    $response = $this->json('post', '/api/device/purge-failed-configs', ['device_id' => 1001]);
    $response->assertStatus(200);

    // Purge removes every download_status === 0 config for the device, so none remain.
    // The 100 non-failed configs created here survive, on top of any pre-existing non-failed ones.
    expect(Config::where('device_id', 1001)->where('download_status', 0)->count())->toEqual(0);
    $remainingConfigsCnt = Config::where('device_id', 1001)->count();
    expect($remainingConfigsCnt)->toEqual($initialNonFailed + 100);
});

afterEach(function () {
    Config::query()->delete();

    $this->rollbackTransaction();
});
