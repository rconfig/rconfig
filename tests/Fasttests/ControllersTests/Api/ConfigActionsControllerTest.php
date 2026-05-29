<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigActionsControllerTest extends TestCase
{
    // this entire config controller is based on fake device 1001

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

    public function test_purge_failed_config()
    {

        // Capture any pre-existing configs for this device so counts stay correct
        // regardless of data already present for device 1001.
        $initialTotal = Config::where('device_id', 1001)->count();
        $initialNonFailed = Config::where('device_id', 1001)->where('download_status', '!=', 0)->count();

        $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 0]);
        $failedConfigsCnt = $configs->where('download_status', 0)->where('device_id', 1001)->count();
        $this->assertEquals(50, $failedConfigsCnt);

        $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 1]);
        $goodConfigsCnt = $configs->where('download_status', 1)->where('device_id', 1001)->count();
        $this->assertEquals(50, $goodConfigsCnt);

        $configs = Config::factory(50)->create(['device_id' => 1001, 'type' => 'device_download', 'download_status' => 2]);
        $unkownConfigsCnt = $configs->where('download_status', 2)->where('device_id', 1001)->count();
        $this->assertEquals(50, $unkownConfigsCnt);

        $totalConfigsCount = Config::where('device_id', 1001)->count();
        $this->assertEquals($initialTotal + 150, $totalConfigsCount);

        $response = $this->json('post', '/api/device/purge-failed-configs', ['device_id' => 1001]);
        $response->assertStatus(200);

        // Purge removes every download_status === 0 config for the device, so none remain.
        // The 100 non-failed configs created here survive, on top of any pre-existing non-failed ones.
        $this->assertEquals(0, Config::where('device_id', 1001)->where('download_status', 0)->count());
        $remainingConfigsCnt = Config::where('device_id', 1001)->count();
        $this->assertEquals($initialNonFailed + 100, $remainingConfigsCnt);
    }

    protected function tearDown(): void
    {
        Config::query()->delete();

        $this->rollbackTransaction();
        parent::tearDown();
    }
}
