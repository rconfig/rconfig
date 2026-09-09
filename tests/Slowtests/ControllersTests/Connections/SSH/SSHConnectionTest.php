<?php

use App\CustomClasses\DeviceRecordPrepare;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Models\Device;
use App\Models\Template;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->device3 = Device::where('id', 1003)->first();
    $this->device4 = Device::where('id', 1004)->first();
    $this->device6 = Device::where('id', 1006)->first();
    $this->device11 = Device::where('id', 1011)->first();

    // check if 10.1.1.170 is reachable
    $dev_cisco_ip = '10.1.1.170';
    $pingresult = exec("ping -c 1 -W 1 $dev_cisco_ip", $outcome, $status);

    if (str_contains($outcome[3], '0 received')) {
        $this->markTestSkipped('Router is not reachable');
    }
});

test('device3 was found', function () {
    $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();

    expect($devicerecord['id'])->toEqual(1003);
    expect($devicerecord['device_name'])->toEqual('router3');
});

test('device3 has commands', function () {
    $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();

    expect(count($devicerecord['commands']))->toBeGreaterThan(0);
});

test('full ss h download from device3 direct from classes', function () {
    // start timer
    $start = microtime(true);

    $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
    $connectionObj = new MainConnectionManager($devicerecord, 0);
    $configsArray = $connectionObj->setupConnectAndReturnOutput();

    // end timer
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);

    expect(count($configsArray))->toBeGreaterThan(0);
});

test('check device can override connection template port but fails connection', function () {
    // start timer
    $start = microtime(true);

    $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
    $devicerecord['device_port_override'] = 2222;
    expect($devicerecord['device_port_override'])->toEqual(2222);

    $connectionObj = new MainConnectionManager($devicerecord, 0);

    $configsArray = $connectionObj->setupConnectAndReturnOutput();
    expect($configsArray['failure'])->toEqual('There was an authentication or connection issue with router3');

    // end timer
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);
});

test('check device can override connection template port but passes connection', function () {
    // start timer
    $start = microtime(true);

    $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
    $devicerecord['device_port_override'] = 22;
    expect($devicerecord['device_port_override'])->toEqual(22);

    $connectionObj = new MainConnectionManager($devicerecord, 0);

    $configsArray = $connectionObj->setupConnectAndReturnOutput();

    // end timer
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);

    expect(count($configsArray))->toBeGreaterThan(0);
});

test('full ss h download from device4 direct from classes', function () {
    // start timer
    $start = microtime(true);
    $devicerecord = (new DeviceRecordPrepare($this->device4))->DeviceRecordToArray();
    $connectionObj = new MainConnectionManager($devicerecord, 0);
    $configsArray = $connectionObj->setupConnectAndReturnOutput();

    // end timer
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);
    expect(count($configsArray))->toBeGreaterThan(0);
});

test('established connection has options params if they are set in the template', function () {
    // start timer
    $start = microtime(true);
    $template = Template::factory()->create([
        'id' => 2000,
        'fileName' => '/app/rconfig/templates/fake_template_with_options.yml',
        'templateName' => 'Fake Template with Options Set',
        'description' => 'Fake Template with Options Set for testing purposes',
    ]);
    if (file_exists(base_path('tests/storage/fake_template_with_options.yml'))) {
        expect(true)->toBeTrue();
    }
    if (! copy(base_path('tests/storage/fake_template_with_options.yml'), storage_path() . '/app/rconfig/templates//fake_template_with_options.yml')) {
        expect(false)->toBeTrue();
        // echo "failed to copy $file...\n";
    }
    if (file_exists(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml')) {
        expect(true)->toBeTrue();
    }

    $devicerecord = (new DeviceRecordPrepare($this->device6))->DeviceRecordToArray();
    $connectionObj = new MainConnectionManager($devicerecord, 0);
    $result = $connectionObj->getAllConnectionParamsArray();

    // end timer
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(5);
    expect($result)->toBeObject();

    // The fixture still authors AnsiHost as "yes", which is the point: TemplateNormaliser
    // settles every switch on 'on' or 'off' right after the YAML parse, and the consumer
    // in SSH\SendCommand compares against the canonical 'on'.
    expect($result->options['AnsiHost'])->toEqual('on');
    expect(240)->toEqual($result->options['setWindowSize'][0]);

    unlink(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml');
    if (! file_exists(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml')) {
        expect(true)->toBeTrue();
    }
});

test('full ssh download and file exists check from command no enable ss h template and regex prompt', function () {
    $start = microtime(true);
    $this->device3['device_main_prompt'] = '.*router.*#';
    $this->assertStringContainsString('.*router.*#', $this->device3['device_main_prompt']);

    Artisan::call('rconfig:download-device 1003');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(downloaded_file_exists_on_disk($this->device3, $match[0]))->toBeTrue();
        }
    }
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(6);

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1003');
    $this->assertStringContainsString($arr[1], 'Start device download for router3 ID:1003');
    $this->assertDatabaseHas('devices', [
        'id' => 1003,
        'status' => 1,
    ]);

    $this->device3->device_main_prompt = 'r1#';
    $this->assertStringContainsString('r1#', $this->device3['device_main_prompt']);
});

test('full ssh download and file exists check from command no enable ss h template', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 1003');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(downloaded_file_exists_on_disk($this->device3, $match[0]))->toBeTrue();
        }
    }
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(6);

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1003');
    $this->assertStringContainsString($arr[1], 'Start device download for router3 ID:1003');
    $this->assertDatabaseHas('devices', [
        'id' => 1003,
        'status' => 1,
    ]);
});

test('v6 full ssh download and file exists check from command no enable ss h template', function () {
    $start = microtime(true);

    Artisan::call('rconfig:download-device 1010');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(6);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(downloaded_file_exists_on_disk($this->device3, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1010');
    $this->assertStringContainsString($arr[1], 'Start device download for router1v6 ID:1010');
    $this->assertDatabaseHas('devices', [
        'id' => 1003,
        'status' => 1,
    ]);
});

test('full ssh download and file exists check from device4 from command enable ss h template', function () {
    $start = microtime(true);
    Artisan::call('rconfig:download-device 1004');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(6);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(downloaded_file_exists_on_disk($this->device4, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1004');
    $this->assertStringContainsString($arr[1], 'Start device download for router4 ID:1004');
    $this->assertDatabaseHas('devices', [
        'id' => 1004,
        'status' => 1,
    ]);
});

function downloaded_file_exists_on_disk($device, $command)
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

function setup_extra_devices()
{
    $dev_cisco_ip = '10.1.1.170';

    // SSH no enable template with case affected prompts
    DB::table('devices')->insert([
        'id' => 10031,
        'device_name' => 'router3',
        'device_ip' => $dev_cisco_ip,
        'device_default_creds_on' => 0,
        'device_username' => 'cisco',
        'device_password' => Crypt::encrypt('cisco'),
        'device_enable_password' => Crypt::encrypt('cisco'),
        'device_main_prompt' => 'ROUter1#',
        'device_enable_prompt' => 'router1>',
        'device_category_id' => 1,
        'device_template' => 3,
        'device_model' => 'CSR1000v',
        'device_version' => '',
        'device_added_by' => 1,
        'status' => 1,
    ]);

    // SSH no enable with invalid prompt to test log event for invalid prompt
    DB::table('devices')->insert([
        'id' => 10032,
        'device_name' => 'router3',
        'device_ip' => $dev_cisco_ip,
        'device_default_creds_on' => 0,
        'device_username' => 'cisco',
        'device_password' => Crypt::encrypt('cisco'),
        'device_enable_password' => Crypt::encrypt('cisco'),
        'device_main_prompt' => 'ROUter2#',
        'device_enable_prompt' => 'router2>',
        'device_category_id' => 1,
        'device_template' => 3,
        'device_model' => 'CSR1000v',
        'device_version' => '',
        'device_added_by' => 1,
        'status' => 1,
    ]);

    DB::table('category_device')->insert([
        'category_id' => 1,
        'device_id' => '10031',
    ]);
    DB::table('device_vendor')->insert([
        'vendor_id' => 1,
        'device_id' => '10031',
    ]);
    DB::table('category_device')->insert([
        'category_id' => 1,
        'device_id' => '10032',
    ]);
    DB::table('device_vendor')->insert([
        'vendor_id' => 1,
        'device_id' => '10032',
    ]);
}

// tear down
function remove_extra_devices()
{
    Device::find(10031)->delete();
    Device::find(10032)->delete();
    DB::table('category_device')->where('device_id', 10031)->delete();
    DB::table('device_vendor')->where('device_id', 10031)->delete();
    DB::table('category_device')->where('device_id', 10032)->delete();
    DB::table('device_vendor')->where('device_id', 10032)->delete();
}
