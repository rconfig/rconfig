<?php

namespace Tests\Slowtests\ControllersTests\Connections\DeviceTests;

use App\CustomClasses\DeviceRecordPrepare;
use App\Models\Config;
use App\Models\Device;
use Artisan;
use File;
use Tests\TestCase;

class MikrotikTests extends TestCase
{
    protected $device;

    protected $template_filename;

    public function setUp(): void
    {
        parent::setUp();

        $this->device = Device::where('id', 20001)->first();
        $this->template_filename = 'mikrotek-ssh-noenable.yml';
    }

    public function test_check_that_mikrotik_in_db()
    {
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
    }

    public function test_can_read_template()
    {
        $contents = File::get(templates_path() . $this->template_filename);
        $this->assertStringContainsString('name: "MikroTek SSH nnoenable"', $contents);
    }

    public function test_device_has_commands()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

        $this->assertGreaterThan(0, count($devicerecord['commands']));
    }

    public function test_download_device_config()
    {
        $this->log_message_during_test(substr(strrchr(__CLASS__, '\\'), 1) . '/' . __FUNCTION__, 'This test will take over 25 seconds to complete.');

        $start = microtime(true);
        $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

        Artisan::call('rconfig:download-device ' . $this->device->id);
        $result = Artisan::output();
        $arr = explode("\n", $result);
        // dd($arr);

        $time = microtime(true) - $start;
        $this->assertLessThan(60, $time);

        // dd($devicPerecord['commands']);

        $this->assertStringContainsString('/interface bridge', $this->return_file_contents($devicerecord['commands'][5189])); // top of the config
        $this->assertStringContainsString('add fast-forward=no name=bridge8', $this->return_file_contents($devicerecord['commands'][5189]));
        $this->assertStringContainsString('add action=dst-nat chain=dstnat dst-address=3.3.3.158 dst-port=80 protocol=', $this->return_file_contents($devicerecord['commands'][5189])); // mid config
        $this->assertStringContainsString('set time-zone-name=America/Los_Angeles', $this->return_file_contents($devicerecord['commands'][5189])); // end of config
        $this->assertStringContainsString('set filter-interface=ether1 filter-ip-address=1.1.1.1/32', $this->return_file_contents($devicerecord['commands'][5189]));

        $this->assertStringContainsString('0  R  ether1                              ether            1500', $this->return_file_contents($devicerecord['commands'][5190])); // top of the config
        $this->assertStringContainsString('11     pppoe-out1                          pppoe-out', $this->return_file_contents($devicerecord['commands'][5190])); // top of the config


        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:' . $this->device->id);
        $this->assertStringContainsString($arr[1], 'Start device download for ' . $this->device->device_name . ' ID:' . $this->device->id);
    }

    private function return_file_contents($command)
    {
        $config = Config::where('device_id', $this->device->id)->where('command', $command)->latest()->first();

        return File::get($config->config_location);
    }

    private function empty_data_directory()
    {
        $gitignore = File::get(config_data_path() . '.gitignore');
        File::cleanDirectory(config_data_path());
        File::put(config_data_path() . '.gitignore', $gitignore);
    }

    protected function tearDown(): void
    {
        $this->empty_data_directory();
    }
}
