<?php

namespace Tests\Slowtests\ControllersTests\Console\Commands;

use App\CustomClasses\DeviceRecordPrepare;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Http\Controllers\Connections\Params\DeviceParams;
use App\Models\Device;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class rconfigDeviceDownloadTest extends TestCase
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

        // this is a placeholder for rconfigDeviceDownload class command, but most tests for this command are extracted to Tests\ControllerTests\Connections\Telnet\TelnetConnectTest
        // i keep this here so i remind myself that the real test for this class are in TelnetConnectTest, but as I mimic the dir strucutre of the commands folder, this stays put.
    }

    public function test_basic_telnet_test()
    {
        // starttime
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1001');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1001');
        $this->assertStringContainsString($arr[1], 'Start device download for router1 ID:1001');
        $this->assertStringContainsString($arr[2], 'Config downloaded for router1 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router1 with command: "show version" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router1 with command: "show run" was successful');
        $this->assertStringContainsString($arr[5], 'End rconfig:download-device');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'End device download for router1 ID:1001',
        ]);
        $this->assertDatabaseHas('devices', [
            'device_name' => 'router1',
            'status' => 1,
        ]);

        //endtime
        $end = microtime(true);
        $time = $end - $start;

        // assert time
        $this->assertLessThan(5, $time);
    }

    public function test_basic_ssh_test()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1003');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1003');
        $this->assertStringContainsString($arr[1], 'Start device download for router3 ID:1003');
        $this->assertStringContainsString($arr[2], 'Config downloaded for router3 with command: "show clock" was successful');
        $this->assertStringContainsString($arr[3], 'Config downloaded for router3 with command: "show version" was successful');
        $this->assertStringContainsString($arr[4], 'Config downloaded for router3 with command: "show run" was successful');
        $this->assertStringContainsString($arr[5], 'End rconfig:download-device');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'End device download for router3 ID:1003',
        ]);
        $this->assertDatabaseHas('devices', [
            'device_name' => 'router3',
            'status' => 1,
        ]);

        $time = microtime(true) - $start;
        $this->assertLessThan(7, $time);
    }

    public function test_failed_auth_message_and_log_and_status_for_telnet_connect()
    {
        $start = microtime(true);

        $this->add_5_sec_timeout_telnet_noenable_template();

        // setting incorrect username
        $this->device1['device_username'] = 'stephen';

        $devicerecord = (new DeviceRecordPrepare($this->device1))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 1);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        $this->remove_5_sec_timeout_telnet_noenable_template();

        $this->assertDatabaseHas('activity_log', [
            'description' => 'There was an authentication or connection issue with router1',
        ]);

        $this->assertDatabaseHas('devices', [
            'device_name' => 'router1',
            'status' => 0,
        ]);

        $time = microtime(true) - $start;
        $this->assertLessThan(30, $time);
    }

    public function test_failed_auth_message_and_log_and_status_for_ssh_connect()
    {
        $start = microtime(true);

        $this->add_5_sec_timeout_ssh_noenable_template();

        $this->device3['device_username'] = 'stephen';

        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        $this->remove_5_sec_timeout_ssh_noenable_template();

        $this->assertStringContainsString($configsArray['failure'], 'There was an authentication or connection issue with router3');

        $this->assertDatabaseHas('activity_log', [
            'description' => 'There was an authentication or connection issue with router3',
        ]);

        $this->assertDatabaseHas('devices', [
            'device_name' => 'router3',
            'status' => 0,
        ]);

        $time = microtime(true) - $start;
        $this->assertLessThan(7, $time);
    }
}
