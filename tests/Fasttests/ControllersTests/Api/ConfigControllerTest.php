<?php

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    Config::truncate();
});

test('get all configs', function () {
    Config::factory(20)->create();
    $response = $this->get('/api/configs?page=1&perPage=100');
    $response->assertStatus(200);
    expect(count($response['data']))->toEqual(20);
});

test('get all configs sorted by created at', function () {
    $olderConfig = Config::factory()->create(['created_at' => Carbon::now()->subDay()]);
    $newerConfig = Config::factory()->create(['created_at' => Carbon::now()->subMinutes(5)]);

    $ascendingResponse = $this->get('/api/configs?page=1&perPage=10&sort=created_at');
    $ascendingResponse->assertStatus(200);
    $ascendingData = $ascendingResponse->json('data');
    expect($ascendingData[0]['id'])->toEqual($olderConfig->id);
    expect($ascendingData[1]['id'])->toEqual($newerConfig->id);

    $descendingResponse = $this->get('/api/configs?page=1&perPage=10&sort=-created_at');
    $descendingResponse->assertStatus(200);
    $descendingData = $descendingResponse->json('data');
    expect($descendingData[0]['id'])->toEqual($newerConfig->id);
    expect($descendingData[1]['id'])->toEqual($olderConfig->id);
});

test('get all configs for given device id', function () {
    Config::factory(20)->create(['device_id' => 1001]);
    $response = $this->get('/api/configs/all-by-deviceid/1001/?page=1&perPage=100&filter=&sortCol=&sortOrd=');
    $response->assertStatus(200);

    // test pagination structure
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

    // Access the response data properly
    $responseData = $response->json();
    expect(count($responseData['data']))->toEqual(20);
    $response->assertStatus(200);
});

test('get all configs for given device id filter by command', function () {
    Config::factory(36)->create(['device_id' => 1001, 'command' => 'show run']);
    Config::factory(100)->create(['device_id' => 1001, 'command' => 'notshow notrun']);

    // Updated URL to use proper Spatie Query Builder filter format
    $response = $this->get('/api/configs/all-by-deviceid/1001?page=1&perPage=200&filter[q]=show run&sortCol=&sortOrd=desc');
    $response->assertStatus(200);

    // test pagination structure
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

    // Access the response data properly
    $responseData = $response->json();
    expect(count($responseData['data']))->toEqual(36);
});

test('get all configs for given device id filter by json command', function () {
    Config::factory(7)->create(['device_id' => 1001, 'command' => 'not commands']);
    Config::factory(3)->create(['device_id' => 1001, 'command' => 'show run']);

    // Updated URL to use proper Spatie Query Builder filter format
    $response = $this->get('/api/configs/all-by-deviceid/1001/?page=1&perPage=100&filter[q]=show run&sortCol=&sortOrd=');
    $response->assertStatus(200);

    // test pagination structure
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

    // Access the response data properly
    $responseData = $response->json();
    expect(count($responseData['data']))->toEqual(3);
});

test('get all configs for given device id filter by date range', function () {
    Config::factory(5)->create(['device_id' => 1001, 'created_at' => Carbon::parse('2026-06-10 09:00:00')]);
    Config::factory(8)->create(['device_id' => 1001, 'created_at' => Carbon::parse('2026-06-15 12:00:00')]);
    Config::factory(4)->create(['device_id' => 1001, 'created_at' => Carbon::parse('2026-06-25 08:00:00')]);

    $response = $this->get('/api/configs/all-by-deviceid/1001?page=1&perPage=100&filter[created_at_between]=2026-06-12,2026-06-20');
    $response->assertStatus(200);

    expect(count($response->json('data')))->toEqual(8);
});

test('get all configs for given device id date range end is inclusive of full day', function () {
    // A config created late on the end date must still be matched.
    Config::factory()->create(['device_id' => 1001, 'created_at' => Carbon::parse('2026-06-20 23:30:00')]);

    $response = $this->get('/api/configs/all-by-deviceid/1001?page=1&perPage=100&filter[created_at_between]=2026-06-20,2026-06-20');
    $response->assertStatus(200);

    expect(count($response->json('data')))->toEqual(1);
});

test('get distinct commands for given device id', function () {
    Config::factory(100)->create(['device_id' => 1001]);
    $response = $this->get('/api/configs/distinct-commands/1001');

    expect(count($response['data']))->toEqual(3);
    $response->assertStatus(200);
});

test('get latest configs for given device id', function () {
    Config::truncate();
    fakeConfigInserts();

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
});

test('show single config', function () {
    $config = Config::factory()->create();
    $response = $this->get('/api/configs/' . $config->id);

    // dd($response);
    $response->assertStatus(200);

    $response->assertJson([
        'id' => $config->id,
        'device_name' => $config->device_name,
        'device_category' => $config->device_category,
    ]);
});

test('get single config file contents and response time for large file', function () {
    $lge_config_file = rconfig_appdir_path() . '/tests/storage/lge_655KB_configfile.txt';
    expect(File::exists($lge_config_file))->toEqual(true);
    expect(File::size($lge_config_file))->toEqual(670987);

    Config::insert([
        'id' => 99999999,
        'device_id' => 99999999,
        'device_name' => 'fortigate',
        'device_category' => 'fortigate',
        'command' => 'show fill-configuration',
        'config_location' => rconfig_appdir_path() . '/tests/storage/lge_655KB_configfile.txt',
        'config_filename' => 'lge_655KB_configfile.txt',
        'config_filesize' => File::size($lge_config_file),
    ]);

    // start time after download
    $start = microtime(true);

    $response = $this->get('/api/configs/view-config/99999999');
    $response->assertStatus(200);

    $this->assertStringContainsString('config system interface', $response->getContent());

    $end = microtime(true);
    $responseTime = $end - $start;
    expect($responseTime)->toBeLessThan(0.2);

    // less than 200ms
    Config::where('id', 99999999)->delete();
});

test('delete config', function () {
    $config = Config::factory()->create();
    if (! File::exists($config->config_location)) {
        File::makeDirectory(dirname($config->config_location), 0777, true, true);
    }

    File::put($config->config_location, 'empty');

    expect($config->config_location)->toBeFile();

    $this->assertDatabaseHas('configs', ['id' => $config->id]);

    $this->delete('/api/configs/' . $config->id);

    $this->assertDatabaseMissing('configs', ['id' => $config->id]);
    $this->assertFileDoesNotExist($config->config_location);
});

function fakeConfigInserts()
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

afterEach(function () {
    Config::query()->delete();

    $this->rollBackTransaction();
});
