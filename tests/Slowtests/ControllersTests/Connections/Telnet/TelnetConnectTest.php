<?php

use App\CustomClasses\DeviceRecordPrepare;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Skip if integration tests not enabled
    if (! env('RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Set RUN_INTEGRATION_TESTS=true to run live Telnet integration tests');
    }

    $this->device1 = Device::where('id', 1001)->first();
    $this->device2 = Device::where('id', 1002)->first();
    $this->device5 = Device::where('id', 1005)->first();

    $device1_params = new DeviceParams($this->device1->toArray());
    $this->device1_params_object = $device1_params->getAllDeviceParams();
});

test('the device was not found', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 12412421');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:12412421');
    $this->assertStringContainsString($arr[1], 'No device records returned. Download will now terminate!');

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);
});

test('the device was found', function () {
    $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();

    expect($devicerecord['id'])->toEqual(1001);
    expect($devicerecord['device_name'])->toEqual('router1');
});

test('the device has commands', function () {
    $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();

    expect(count($devicerecord['commands']))->toBeGreaterThan(0);
});

test('full telnet download from device direct from classes', function () {
    $start = microtime(true);

    $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();
    $connectionObj = new MainConnectionManager($devicerecord, 0);
    $configsArray = $connectionObj->setupConnectAndReturnOutput();

    expect(count($configsArray))->toBeGreaterThan(0);
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);
});

test('full telnet download and file exists check from device from command no enable template', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 1001');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(10);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(telnetDownloadedFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1001');
    $this->assertStringContainsString($arr[1], 'Start device download for router1 ID:1001');
    $this->assertDatabaseHas('devices', [
        'id' => 1001,
        'status' => 1,
    ]);
    $this->assertDatabaseHas('configs', [
        'device_id' => 1001,
        'type' => 'device_download',
        'download_status' => 1,
    ]);
});

test('v6 full telnet download and file exists check from device from command no enable template', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 1009');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(telnetDownloadedFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1009');
    $this->assertStringContainsString($arr[1], 'Start device download for router1v6 ID:1009');
    $this->assertDatabaseHas('devices', [
        'id' => 1009,
        'status' => 1,
    ]);
    $this->assertDatabaseHas('configs', [
        'device_id' => 1009,
        'type' => 'device_download',
        'download_status' => 1,
    ]);
});

test('telnet log error if prompt is not matched within specified time which implies an incorrect prompt', function () {
    // setup extra devices for testing
    telnetSetupExtraDevices();

    $this->log_message_during_test('TelnetConnectTest/telnet log error if prompt is not matched within specified time which implies an incorrect prompt', 'This test will take over 25 seconds to complete.');

    $this->add_5_sec_timeout_telnet_noenable_template();

    $start = microtime(true);

    Artisan::call('rconfig:download-device 10011');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeGreaterThan(10);

    // 5 second template timeout times number of commands (at least 3 commands in template)
    $this->remove_5_sec_timeout_telnet_noenable_template();

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(telnetDownloadedFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:10011');
    $this->assertStringContainsString($arr[1], 'Start device download for router1 ID:10011');
    $this->assertStringContainsString($arr[2], 'No config data returned for router1 - ID:10011. Check your logs for more information');

    $this->assertDatabaseHas('devices', [
        'id' => 10011,
        'status' => 0,
    ]);

    $this->assertDatabaseHas('configs', [
        'device_id' => 10011,
        'type' => 'device_download',
        'download_status' => 0,
    ]);

    // database should have a log entry for the error
    $this->assertDatabaseHas('activity_log', [
        'device_id' => 10011,
        'event_type' => 'connection',
        'description' => 'Authentication Failed for 10.1.1.170 ID:10011. Or wrong prompt configured for this device! Check your device settings.',
    ]);

    telnetRemoveExtraDevices();
});

test('full telnet download and file exists check from device from command enable template', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 1002');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(telnetDownloadedFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1002');
    $this->assertStringContainsString($arr[1], 'Start device download for router2 ID:1002');
    $this->assertDatabaseHas('devices', [
        'id' => 1002,
        'status' => 1,
    ]);
});

test('full telnet download from unreachable device direct from classes', function () {
    $this->log_message_during_test('TelnetConnectTest/full telnet download from unreachable device direct from classes', 'This test will take over 10 seconds to complete.');

    $this->add_5_sec_timeout_telnet_noenable_template();

    $start = microtime(true);

    $devicerecord = (new DeviceRecordPrepare($this->device5))->DeviceRecordToArray();
    $connectionObj = new MainConnectionManager($devicerecord, 0);
    $configsArray = $connectionObj->setupConnectAndReturnOutput();

    expect($configsArray)->toBeFalse();

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(10);

    $this->remove_5_sec_timeout_telnet_noenable_template();

    // assert log has
    $this->assertDatabaseHas('activity_log', [
        'device_id' => 1005,
        'event_type' => 'connection',
        'description' => 'Unable to connect to 192.169.1.1 - ID:1005',
    ]);
});

test('full telnet download from unreachable device from command', function () {
    $this->log_message_during_test('TelnetConnectTest/full telnet download from unreachable device from command', 'This test will take over 10 seconds to complete.');

    $this->add_5_sec_timeout_telnet_noenable_template();

    $start = microtime(true);

    Artisan::call('rconfig:download-device 1005');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(10);

    $this->remove_5_sec_timeout_telnet_noenable_template();

    $this->assertDatabaseHas('devices', [
        'id' => 1005,
        'status' => 0,
    ]);

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1005');
    $this->assertStringContainsString($arr[1], 'Start device download for router5 ID:1005');
    $this->assertStringContainsString($arr[2], 'No config data returned for router5 - ID:1005. Check your logs for more information');
});

function telnetDownloadedFileExistsOnDisk($device, $command)
{
    $fileops = new FileOperations(
        $command,
        $device['category'][0]['categoryName'],
        $device['device_name'],
        $device['id'],
        config_data_path(),
        'device_download'
    );

    $fullpath = $fileops->createFile($command);

    return File::exists($fullpath);
}

function telnetSetupExtraDevices()
{
    $dev_cisco_ip = '10.1.1.170';

    // incorrect prompt template to test for log error on download
    DB::table('devices')->insert([
        'id' => 10011,
        'device_name' => 'router1',
        'device_ip' => $dev_cisco_ip,
        'device_default_creds_on' => 0,
        'device_username' => 'cisco',
        'device_password' => Crypt::encrypt('cisco'),
        'device_enable_password' => Crypt::encrypt('cisco'),
        'device_main_prompt' => 'router2#',
        'device_enable_prompt' => 'router2>',
        'device_category_id' => 1,
        'device_template' => 1,
        'device_model' => 'CSR1000v',
        'device_version' => '',
        'device_added_by' => 1,
        'status' => 1,
    ]);

    DB::table('device_template')->insert([
        'template_id' => 1,
        'device_id' => '10011',
    ]);
    DB::table('category_device')->insert([
        'category_id' => 1,
        'device_id' => '10011',
    ]);
    DB::table('device_vendor')->insert([
        'vendor_id' => 1,
        'device_id' => '10011',
    ]);
    DB::table('device_tag')->insert([
        'device_id' => 10011,
        'tag_id' => 1001,
    ]);
}

// tear down
function telnetRemoveExtraDevices()
{
    Device::find(10011)->delete();
    DB::table('device_template')->where('device_id', 10011)->delete();
    DB::table('category_device')->where('device_id', 10011)->delete();
    DB::table('device_vendor')->where('device_id', 10011)->delete();
    DB::table('device_tag')->where('device_id', 10011)->delete();
}
