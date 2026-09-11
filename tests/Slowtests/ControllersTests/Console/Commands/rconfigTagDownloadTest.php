<?php

use App\CustomClasses\GetAndCheckTagIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->device1 = Device::where('id', 1001)->first();
    $this->device2 = Device::where('id', 1002)->first();
    $this->device5 = Device::where('id', 1005)->first();

    // tags and relationships with devices are seeded in DeviceTableSeeder class
    $device1_params = new DeviceParams($this->device1->toArray());
    $this->device1_params_object = $device1_params->getAllDeviceParams();
});

test('tag was not found', function () {
    Artisan::call('rconfig:download-tag 2123123');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Start rconfig:download-tag IDs:2123123');
    $this->assertStringContainsString($arr[1], 'No tag records returned. Download will now terminate!');
});

test('the tag was found and routers tag has devices', function () {
    $tagrecords = (new GetAndCheckTagIds([1001, 1002, 1003]))->GetTagRecords();

    // 1000 = devtag1  & 1002 = devtag2 & 1003 = devtag3 which are seeded categories
    // dd($tagrecords);
    expect($tagrecords)->toHaveCount(3);
    expect($tagrecords[0]->device->count())->toBeGreaterThan(0);
    expect($tagrecords[1]->device->count())->toBeGreaterThan(0);
    expect($tagrecords[2]->device->count())->toBeGreaterThan(0);
});

test('tag did not contain devices', function () {
    Artisan::call('rconfig:download-tag 1010');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Start rconfig:download-tag IDs:1010');
    $this->assertStringContainsString($arr[1], 'No devices returned for this category with ID: 1010. Downloader will try next category in the list, or terminate!');
});

test('full telnet download for given tags', function () {
    Artisan::call('rconfig:download-tag 1001 1002');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    foreach ($arr as $line) {
        preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
        if (! empty($match)) {
            expect(tagDownloadFileExistsOnDisk($this->device2, $match[0]))->toBeTrue();
        }
    }

    expect(count($arr))->toBeGreaterThan(0);
    $this->assertStringContainsString($arr[0], 'Start rconfig:download-tag IDs:1001 1002');
    $this->assertStringContainsString($arr[1], 'Start device download for router1 ID:1001');
    $this->assertStringContainsString($arr[2], 'Config downloaded for router1 with command: "show clock" was successful');
    $this->assertStringContainsString($arr[3], 'Config downloaded for router1 with command: "show version" was successful');
    $this->assertStringContainsString($arr[4], 'Config downloaded for router1 with command: "show run" was successful');
    $this->assertStringContainsString($arr[5], 'Start device download for router2 ID:1002');
    $this->assertStringContainsString($arr[8], 'Config downloaded for router2 with command: "show run" was successful');
    $this->assertDatabaseHas('devices', [
        'id' => 1001,
        'status' => 1,
    ]);
});

function tagDownloadFileExistsOnDisk($device, $command)
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
