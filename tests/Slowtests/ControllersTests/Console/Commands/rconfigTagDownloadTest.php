<?php

namespace Tests\Slowtests\ControllersTests\Console\Commands;

use App\CustomClasses\GetAndCheckTagIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class rconfigTagDownloadTest extends TestCase
{
    protected $user;

    protected $device;

    protected $tags;

    protected $device1_params_object;

    public function setUp(): void
    {
        parent::setUp();
        $this->device1 = Device::where('id', 1001)->first();
        $this->device2 = Device::where('id', 1002)->first();
        $this->device5 = Device::where('id', 1005)->first();

        // tags and relationships with devices are seeded in DeviceTableSeeder class

        $device1_params = new DeviceParams($this->device1->toArray());
        $this->device1_params_object = $device1_params->getAllDeviceParams();
    }

    public function test_tag_was_not_found()
    {
        Artisan::call('rconfig:download-tag 2123123');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-tag IDs:2123123');
        $this->assertStringContainsString($arr[1], 'No tag records returned. Download will now terminate!');
    }

    public function test_the_tag_was_found_and_routers_tag_has_devices()
    {
        $tagrecords = (new GetAndCheckTagIds([1001, 1002, 1003]))->GetTagRecords(); // 1000 = devtag1  & 1002 = devtag2 & 1003 = devtag3 which are seeded categories
        // dd($tagrecords);

        $this->assertCount(3, $tagrecords);
        $this->assertGreaterThan(0, $tagrecords[0]->device->count());
        $this->assertGreaterThan(0, $tagrecords[1]->device->count());
        $this->assertGreaterThan(0, $tagrecords[2]->device->count());
    }

    public function test_tag_did_not_contain_devices()
    {
        Artisan::call('rconfig:download-tag 1010');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-tag IDs:1010');
        $this->assertStringContainsString($arr[1], 'No devices returned for this category with ID: 1010. Downloader will try next category in the list, or terminate!');
    }
 
    public function test_full_telnet_download_for_given_tags()
    {
        Artisan::call('rconfig:download-tag 1001 1002');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
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
    }

    private function downloaded_file_exists_on_disk($device, $command)
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
}
