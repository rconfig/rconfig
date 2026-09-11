<?php

use App\CustomClasses\GetAndCheckCategoryIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;

beforeEach(function () {
    $this->device1 = Device::where('id', 1001)->first();
    $this->device2 = Device::where('id', 1002)->first();
    $this->device3 = Device::where('id', 1003)->first();
    $this->device5 = Device::where('id', 1005)->first();

    $device1_params = new DeviceParams($this->device1->toArray());
    $this->device1_params_object = $device1_params->getAllDeviceParams();
});

test('category was not found', function () {
    Artisan::call('rconfig:download-category 2123123');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Start rconfig:download-category IDs:2123123');
    $this->assertStringContainsString($arr[1], 'No category records returned. Download will now terminate!');
});

test('category did not contain devices', function () {
    Artisan::call('rconfig:download-category 1010');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Start rconfig:download-category IDs:1010');
    $this->assertStringContainsString($arr[1], 'No devices returned for this category with ID: 1010. Downloader will try next category in the list, or terminate!');
});

test('the category was found and routers category has devices', function () {
    $categoryrecords = (new GetAndCheckCategoryIds([1, 2]))->GetCategoryRecords();

    // 1 = routers & 2 = switches which are seeded categories
    expect($categoryrecords)->toHaveCount(2);
    expect($categoryrecords[0]->device->count())->toBeGreaterThan(1);
});

test('full telnet and ss h download for given categories', function () {
    $this->log_message_during_test('rconfigCatDownloadTest/full telnet and ssh download for given categories', 'This test will take around 20 seconds to complete.');

    $this->add_5_sec_timeout_telnet_noenable_template();

    // start time
    $start = microtime(true);

    Artisan::call('rconfig:download-category 1 2');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $time = microtime(true) - $start;
    expect($time)->toBeLessThan(35);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match);
        if (! empty($match)) {
            expect(catDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    $output = implode("\n", $arr);

    expect(count($arr))->toBeGreaterThan(0);

    // Verify category start
    $this->assertStringContainsString('Start rconfig:download-category IDs:1 2', $output);

    // Verify successful downloads (router1-4)
    $this->assertStringContainsString('Start device download for router1 ID:1001', $output);
    $this->assertStringContainsString('Config downloaded for router1 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1 with command: "show run" was successful', $output);

    $this->assertStringContainsString('Start device download for router2 ID:1002', $output);
    $this->assertStringContainsString('Config downloaded for router2 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router2 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router2 with command: "show run" was successful', $output);

    $this->assertStringContainsString('Start device download for router3 ID:1003', $output);
    $this->assertStringContainsString('Config downloaded for router3 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router3 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router3 with command: "show run" was successful', $output);

    $this->assertStringContainsString('Start device download for router4 ID:1004', $output);
    $this->assertStringContainsString('Config downloaded for router4 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router4 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router4 with command: "show run" was successful', $output);

    // Verify failed/unreachable downloads
    $this->assertStringContainsString('Start device download for router5 ID:1005', $output);
    $this->assertStringContainsString('No config data returned for router5 - ID:1005', $output);

    // router1v6 devices reach the same Cisco device over IPv6 and download successfully (category 1 commands)
    $this->assertStringContainsString('Start device download for router1v6 ID:1009', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show clock" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show version" was successful', $output);
    $this->assertStringContainsString('Config downloaded for router1v6 with command: "show run" was successful', $output);

    $this->assertStringContainsString('Start device download for router1v6 ID:1010', $output);

    // Verify category 2 has no devices
    $this->assertStringContainsString('No devices returned for this category with ID: 2. Downloader will try next category in the list, or terminate!', $output);

    // Verify end message
    $this->assertStringContainsString('End rconfig:download-category', $output);

    // Verify database status for successful device
    $this->assertDatabaseHas('devices', [
        'id' => 1001,
        'status' => 1,
    ]);

    $this->remove_5_sec_timeout_telnet_noenable_template();
});

function catDownloadFileExistsOnDisk($device, $command)
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
