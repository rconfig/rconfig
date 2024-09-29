<?php

namespace Tests\Slowtests\ControllersTests\Connections\Telnet;

use App\CustomClasses\DeviceRecordPrepare;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Crypt;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TelnetConnectTest extends TestCase
{
    protected $user;

    protected $device;

    protected $device1;

    protected $device2;

    protected $device5;

    protected $device1_params_object;

    public function setUp(): void
    {
        parent::setUp();

        $this->device1 = Device::where('id', 1001)->first();
        $this->device2 = Device::where('id', 1002)->first();
        $this->device5 = Device::where('id', 1005)->first();

        $device1_params = new DeviceParams($this->device1->toArray());
        $this->device1_params_object = $device1_params->getAllDeviceParams();
    }

    public function test_the_device_was_not_found()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 12412421');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:12412421');
        $this->assertStringContainsString($arr[1], 'No device records returned. Download will now terminate!');

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);
    }

    public function test_the_device_was_found()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();

        $this->assertEquals(1001, $devicerecord['id']);
        $this->assertEquals('router1', $devicerecord['device_name']);
    }

    public function test_the_device_has_commands()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();

        $this->assertGreaterThan(0, count($devicerecord['commands']));
    }

    public function test_full_telnet_download_from_device_direct_from_classes()
    {
        $start = microtime(true);

        $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        $this->assertGreaterThan(0, count($configsArray));
        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);
    }

    public function test_full_telnet_download_and_file_exists_check_from_device_from_command_no_enable_template()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1001');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
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
    }

    public function test_V6_full_telnet_download_and_file_exists_check_from_device_from_command_no_enable_template()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1009');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
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
    }

    public function test_telnet_log_error_if_prompt_is_not_matched_within_specified_time_which_implies_an_incorrect_prompt()
    {
        // setup extra devices for testing
        $this->setup_extra_devices();

        $this->log_message_during_test(substr(strrchr(__CLASS__, '\\'), 1) . '/' . __FUNCTION__, 'This test will take over 25 seconds to complete.');

        $this->add_5_sec_timeout_telnet_noenable_template();

        $start = microtime(true);

        Artisan::call('rconfig:download-device 10011');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertGreaterThan(10, $time); // 5 second template timeout times number of commands (at least 3 commands in template)

        $this->remove_5_sec_timeout_telnet_noenable_template();

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
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
            'description' => 'Authentication Failed for 192.168.1.170 ID:10011. Or wrong prompt configured for this device! Check your device settings.',
        ]);

        $this->remove_extra_devices();
    }

    public function test_full_telnet_download_and_file_exists_check_from_device_from_command_enable_template()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1002');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device2, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1002');
        $this->assertStringContainsString($arr[1], 'Start device download for router2 ID:1002');
        $this->assertDatabaseHas('devices', [
            'id' => 1002,
            'status' => 1,
        ]);
    }

    public function test_full_telnet_download_from_unreachable_device_direct_from_classes()
    {
        $this->log_message_during_test(substr(strrchr(__CLASS__, '\\'), 1) . '/' . __FUNCTION__ . '/' . __FUNCTION__, 'This test will take over 10 seconds to complete.');

        $this->add_5_sec_timeout_telnet_noenable_template();

        $start = microtime(true);

        $devicerecord = (new DeviceRecordPrepare($this->device5))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        $this->assertFalse($configsArray);

        $time = microtime(true) - $start;
        $this->assertLessThan(10, $time);

        $this->remove_5_sec_timeout_telnet_noenable_template();

        //assert log has
        $this->assertDatabaseHas('activity_log', [
            'device_id' => 1005,
            'event_type' => 'connection',
            'description' => 'Unable to connect to 192.169.1.1 - ID:1005',
        ]);
    }

    public function test_full_telnet_download_from_unreachable_device_from_command()
    {
        $this->log_message_during_test(substr(strrchr(__CLASS__, '\\'), 1) . '/' . __FUNCTION__ . '/' . __FUNCTION__, 'This test will take over 10 seconds to complete.');

        $this->add_5_sec_timeout_telnet_noenable_template();

        $start = microtime(true);

        Artisan::call('rconfig:download-device 1005');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(10, $time);

        $this->remove_5_sec_timeout_telnet_noenable_template();

        $this->assertDatabaseHas('devices', [
            'id' => 1005,
            'status' => 0,
        ]);

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1005');
        $this->assertStringContainsString($arr[1], 'Start device download for router5 ID:1005');
        $this->assertStringContainsString($arr[2], 'No config data returned for router5 - ID:1005. Check your logs for more information');
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

    private function setup_extra_devices()
    {
        $dev_cisco_ip = '192.168.1.170';

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
    private function remove_extra_devices()
    {
        Device::find(10011)->delete();
        DB::table('device_template')->where('device_id', 10011)->delete();
        DB::table('category_device')->where('device_id', 10011)->delete();
        DB::table('device_vendor')->where('device_id', 10011)->delete();
        DB::table('device_tag')->where('device_id', 10011)->delete();
    }
}
