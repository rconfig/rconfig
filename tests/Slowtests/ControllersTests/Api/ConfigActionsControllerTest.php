<?php

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('start download now test', function () {
    $response = $this->json('post', '/api/device/download-now', ['device_id' => 1001]);
    $response->assertStatus(200);
    $this->assertDatabaseHas('activity_log', ['device_id' => 1001, 'description' => 'Config downloaded for router1 with command: "show clock" was successful']);
    $this->assertDatabaseHas('configs', ['device_id' => 1001, 'download_status' => 1, 'type' => 'device_download', 'command' => 'show clock']);
    $response->assertStatus(200);
    $lastestConfig = Config::where('device_id', 1001)->orderBy('id', 'desc')->first();
    expect($lastestConfig->config_location)->toBeFile();
    $fileContents = File::get($lastestConfig->config_location);
    $this->assertStringContainsString('ipv6 address 2A01:AC:1000:700::170/64', $fileContents);
});

afterEach(function () {
    Config::query()->delete();

    $this->rollbackTransaction();
});
