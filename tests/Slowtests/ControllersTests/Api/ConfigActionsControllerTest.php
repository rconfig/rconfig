<?php

namespace Tests\Slowtests\ControllersTests\Api;

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigActionsControllerTest extends TestCase
{
    // Exercises the real download-now API endpoint against the lab-fixture device
    // (id 1001, router1, 10.1.1.170) that DeviceTableSeeder seeds for this suite, so it
    // performs a genuine SSH/Telnet download rather than anything mockable with a factory.

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_start_download_now_test()
    {
        $response = $this->json('post', '/api/device/download-now', ['device_id' => 1001]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('activity_log', ['device_id' => 1001, 'description' => 'Config downloaded for router1 with command: "show clock" was successful']);
        $this->assertDatabaseHas('configs', ['device_id' => 1001, 'download_status' => 1, 'type' => 'device_download', 'command' => 'show clock']);
        $response->assertStatus(200);
        $lastestConfig = Config::where('device_id', 1001)->orderBy('id', 'desc')->first();
        $this->assertFileExists($lastestConfig->config_location);
        $fileContents = File::get($lastestConfig->config_location);
        $this->assertStringContainsString('ipv6 address 2A01:AC:1000:700::170/64', $fileContents);
    }

    protected function tearDown(): void
    {
        Config::query()->delete();

        $this->rollbackTransaction();
        parent::tearDown();
    }
}
