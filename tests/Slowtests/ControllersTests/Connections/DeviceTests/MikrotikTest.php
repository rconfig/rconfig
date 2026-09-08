<?php

use App\CustomClasses\DeviceRecordPrepare;
use App\Models\Config;
use App\Models\Device;

beforeEach(function () {
    $this->device = Device::where('id', 20001)->first();
    $this->template_filename = 'mikrotik-routeros-ssh-noenable.yml';
});

test('check that mikrotik in db', function () {
    $this->assertDatabaseHas('devices', [
        'device_name' => 'mikrotik',
    ]);

    $this->assertDatabaseHas('templates', [
        'fileName' => '/app/rconfig/templates/' . $this->template_filename,
    ]);

    $this->assertDatabaseHas('categories', [
        'categoryName' => 'mikrotik',
    ]);

    $this->assertDatabaseHas('commands', [
        'command' => 'export',
    ]);

    $this->assertDatabaseHas('commands', [
        'command' => 'interface print',
    ]);
});

test('can read template', function () {
    $contents = File::get(templates_path() . $this->template_filename);
    $this->assertStringContainsString('name: "MikroTik RouterOS - SSH - No Enable"', $contents);
});

test('device has commands', function () {
    $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

    expect(count($devicerecord['commands']))->toBeGreaterThan(0);
});

test('download device config', function () {
    $this->log_message_during_test('MikrotikTests/download device config', 'This test will take over 25 seconds to complete.');

    $start = microtime(true);
    $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

    Artisan::call('rconfig:download-device ' . $this->device->id);
    $result = Artisan::output();
    $arr = explode("\n", $result);

    // dd($arr);
    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(60);

    // dd($devicPerecord['commands']);
    $this->assertStringContainsString('/interface bridge', return_file_contents($devicerecord['commands'][5189], $this->device->id));
    // top of the config
    $this->assertStringContainsString('add fast-forward=no name=bridge8', return_file_contents($devicerecord['commands'][5189], $this->device->id));
    $this->assertStringContainsString('add action=dst-nat chain=dstnat dst-address=3.3.3.158 dst-port=80 protocol=', return_file_contents($devicerecord['commands'][5189], $this->device->id));
    // mid config
    $this->assertStringContainsString('set time-zone-name=America/Los_Angeles', return_file_contents($devicerecord['commands'][5189], $this->device->id));
    // end of config
    $this->assertStringContainsString('set filter-interface=ether1 filter-ip-address=1.1.1.1/32', return_file_contents($devicerecord['commands'][5189], $this->device->id));

    $this->assertStringContainsString('0  R  ether1                              ether            1500', return_file_contents($devicerecord['commands'][5190], $this->device->id));
    // top of the config
    $this->assertStringContainsString('11     pppoe-out1                          pppoe-out', return_file_contents($devicerecord['commands'][5190], $this->device->id));

    // top of the config
    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:' . $this->device->id);
    $this->assertStringContainsString($arr[1], 'Start device download for ' . $this->device->device_name . ' ID:' . $this->device->id);
});

function return_file_contents($command, $deviceId)
{
    $config = Config::where('device_id', $deviceId)->where('command', $command)->latest()->first();

    return File::get($config->config_location);
}

function empty_data_directory()
{
    $gitignore = File::get(config_data_path() . '.gitignore');
    File::cleanDirectory(config_data_path());
    File::put(config_data_path() . '.gitignore', $gitignore);
}

afterEach(function () {
    empty_data_directory();
});
