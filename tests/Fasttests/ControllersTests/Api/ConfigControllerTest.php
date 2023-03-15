<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ConfigControllerTest extends TestCase
{
    // this entire config controller is based on fake device 1001

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }
    //TODO: Load NTT Data
    //TODO: PerF check NTT Data

    /** @test */
    public function get_all_configs()
    {
        Config::factory(100)->create();
        $response = $this->get('/api/configs?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    /** @test */
    public function get_all_configs_for_given_device_id()
    {
        Config::factory(100)->create(['device_id' => 1001]);
        $response = $this->get('/api/configs/all-by-deviceid/1001/all/?page=1&perPage=100&filter=&sortCol=&sortOrd=');
        // test pagniation structure
        $response->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);
        $this->assertEquals(100, count($response['data'])); // +++ for default seeded rows
        $response->assertStatus(200);
    }

    /** @test */
    public function get_distinct_commands_for_given_device_id()
    {
        Config::factory(100)->create(['device_id' => 1001]);
        $response = $this->get('/api/configs/distinct-commands/1001');

        $this->assertEquals(3, count($response['data']));
        $response->assertStatus(200);
    }

    /** @test */
    public function get_latest_configs_for_given_device_id()
    {
        Config::truncate();
        $this->fakeConfigInserts();

        $response = $this->get('/api/configs/latest-by-deviceid/1001');

        $response->assertJsonFragment([
            'id' => 24,
            'command' => 'show clock',
            'device_id' => 1001,
        ]);
        $response->assertJsonFragment([
            'id' => 25,
            'command' => 'show run',
            'device_id' => 1001,
        ]);
        $response->assertJsonFragment([
            'id' => 23,
            'command' => 'show version',
            'device_id' => 1001,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function show_single_config()
    {
        $config = Config::factory()->create();
        $response = $this->get('/api/configs/' . $config->id);
        // dd($response);

        $response->assertJson([
            'id' => $config->id,
            'device_name' => $config->device_name,
            'device_category' => $config->device_category,
        ]);
    }

    /** @test */
    public function get_single_config_file_contents()
    {
        Artisan::call('rconfig:download-device 1001');

        $response = $this->get('/api/configs/latest-by-deviceid/1001');
        $id = $response->json()['data'][1]['id']; // for the show run

        $response = $this->get('/api/configs/view-config/' . $id);
        $response->assertStatus(200);
        $this->assertStringContainsString('service timestamps debug datetime msec', $response->getContent());
    }

    /** @test */
    public function delete_config()
    {
        $config = Config::factory()->create();
        if (!File::exists($config->config_location)) {
            File::makeDirectory(dirname($config->config_location), 0777, true, true);
        }

        File::put($config->config_location, 'empty');

        $this->assertFileExists($config->config_location);

        $this->assertDatabaseHas('configs', ['id' => $config->id]);

        $this->delete('/api/configs/' . $config->id);

        $this->assertDatabaseMissing('configs', ['id' => $config->id]);
        $this->assertFileDoesNotExist($config->config_location);
    }

    public function fakeConfigInserts()
    {
        $configs = [
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showclock_1705.txt',
                'config_filename' => 'showclock_1705.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showversion_1705.txt',
                'config_filename' => 'showversion_1705.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showrun_1705.txt',
                'config_filename' => 'showrun_1705.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showclock_1707.txt',
                'config_filename' => 'showclock_1707.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showversion_1707.txt',
                'config_filename' => 'showversion_1707.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showrun_1707.txt',
                'config_filename' => 'showrun_1707.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showclock_1707.txt',
                'config_filename' => 'showclock_1707.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:07:39',
                'end_time' => '2022-01-15 17:07:40',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:40',
                'updated_at' => '2022-01-15 17:07:40',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showrun_1707.txt',
                'config_filename' => 'showrun_1707.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:07:39',
                'end_time' => '2022-01-15 17:07:40',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:40',
                'updated_at' => '2022-01-15 17:07:40',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showclock_1717.txt',
                'config_filename' => 'showclock_1717.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:17:26',
                'end_time' => '2022-01-15 17:17:27',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:27',
                'updated_at' => '2022-01-15 17:17:27',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showversion_1717.txt',
                'config_filename' => 'showversion_1717.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:26',
                'end_time' => '2022-01-15 17:17:27',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:27',
                'updated_at' => '2022-01-15 17:17:27',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showclock_1717.txt',
                'config_filename' => 'showclock_1717.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showversion_1717.txt',
                'config_filename' => 'showversion_1717.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1002,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/15/showversion_1717.txt',
                'config_filename' => 'showversion_1717.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showclock_1708.txt',
                'config_filename' => 'showclock_1708.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showversion_1708.txt',
                'config_filename' => 'showversion_1708.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1708.txt',
                'config_filename' => 'showrun_1708.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showclock_1709.txt',
                'config_filename' => 'showclock_1709.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showversion_1709.txt',
                'config_filename' => 'showversion_1709.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1709.txt',
                'config_filename' => 'showrun_1709.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showclock_1710.txt',
                'config_filename' => 'showclock_1710.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showversion_1710.txt',
                'config_filename' => 'showversion_1710.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txtxxx',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txtxxx',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txtxxx',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1002,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1003,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1004,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/storage/app/rconfig/data/Routers/router1/2022/Jan/17/showrun_1710.txt',
                'config_filename' => 'showrun_1710.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
        ];
        Config::insert($configs);
    }
}
