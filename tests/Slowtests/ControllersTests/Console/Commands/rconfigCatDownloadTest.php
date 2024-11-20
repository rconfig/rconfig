<?php

namespace Tests\Slowtests\ControllersTests\Console\Commands;

use App\CustomClasses\GetAndCheckCategoryIds;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Artisan;
use File;
use Tests\TestCase;

class rconfigCatDownloadTest extends TestCase
{
    protected $user;

    protected $device;

    protected $device1;

    protected $device2;

    protected $device3;

    protected $device5;

    protected $device1_params_object;

    public function setUp(): void
    {
        parent::setUp();
        $this->device1 = Device::where('id', 1001)->first();
        $this->device2 = Device::where('id', 1002)->first();
        $this->device3 = Device::where('id', 1003)->first();
        $this->device5 = Device::where('id', 1005)->first();

        $device1_params = new DeviceParams($this->device1->toArray());
        $this->device1_params_object = $device1_params->getAllDeviceParams();
    }

    public function test_category_was_not_found()
    {
        Artisan::call('rconfig:download-category 2123123');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-category IDs:2123123');
        $this->assertStringContainsString($arr[1], 'No category records returned. Download will now terminate!');
    }

    public function test_category_did_not_contain_devices()
    {
        Artisan::call('rconfig:download-category 1010');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-category IDs:1010');
        $this->assertStringContainsString($arr[1], 'No devices returned for this category with ID: 1010. Downloader will try next category in the list, or terminate!');
    }

    public function test_the_category_was_found_and_routers_category_has_devices()
    {
        $categoryrecords = (new GetAndCheckCategoryIds([1, 2]))->GetCategoryRecords(); // 1 = routers & 2 = switches which are seeded categories

        $this->assertCount(2, $categoryrecords);
        $this->assertGreaterThan(1, $categoryrecords[0]->device->count());
    }
 
    public function test_full_telnet_and_SSH_download_for_given_categories()
    {
        $this->log_message_during_test(substr(strrchr(__CLASS__, "\\"), 1) . '/' . __FUNCTION__, 'This test will take around 20 seconds to complete.');

        $this->add_5_sec_timeout_telnet_noenable_template();

        // start time
        $start = microtime(true);

        Artisan::call('rconfig:download-category 1 2');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(35, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-category IDs:1 2');
        $this->assertStringContainsString($arr[1], 'Start device download for router1 ID:1001');
        $this->assertStringContainsString($arr[2], 'Config downloaded for router1 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router1 with command: "show version" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router1 with command: "show run" was successful');
        $this->assertStringContainsString($arr[5], 'Start device download for router2 ID:1002');
        $this->assertStringContainsString($arr[9], 'Start device download for router3 ID:1003');
        $this->assertStringContainsString($arr[13], 'Start device download for router4 ID:1004');
        $this->assertStringContainsString($arr[17], 'Start device download for router5 ID:1005');
        $this->assertStringContainsString($arr[23], 'Config downloaded for router1v6 with command: "show run" was successful');
        $this->assertStringContainsString($arr[25], 'No devices returned for this category with ID: 2. Downloader will try next category in the list, or terminate!');
        $this->assertDatabaseHas('devices', [
            'id' => 1001,
            'status' => 1,
        ]);

        $this->remove_5_sec_timeout_telnet_noenable_template();
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
