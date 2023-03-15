<?php

namespace Tests\Slowtests\ControllersTests\Connections\DeviceTests;

use App\CustomClasses\DeviceRecordPrepare;
use App\Models\Command;
use App\Models\Config;
use App\Models\Device;
use Artisan;
use File;
use Predis\Configuration\Option\Commands;
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

    /** @test */
    public function check_that_mikrotik_in_db()
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

    /** @test */
    public function can_read_template()
    {
        $contents = File::get(templates_path() . $this->template_filename);
        $this->assertStringContainsString('name: "MikroTek SSH nnoenable"', $contents);
    }

    /** @test */
    public function device_has_commands()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

        $this->assertGreaterThan(0, count($devicerecord['commands']));
    }

    /** @test */
    public function download_device_config()
    {
        $this->log_message_during_test(substr(strrchr(__CLASS__, "\\"), 1) . '/' . __FUNCTION__, 'This test will take over 25 seconds to complete.');

        $start = microtime(true);
        $devicerecord = (new DeviceRecordPrepare($this->device))->DeviceRecordToArray();

        Artisan::call('rconfig:download-device ' . $this->device->id);
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(60, $time);

        $this->assertStringContainsString('interface bridge', $this->return_file_contents($devicerecord['commands'][5189]));
        $this->assertStringContainsString('add action=accept chain=input', $this->return_file_contents($devicerecord['commands'][5189]));
        $this->assertStringContainsString('set filter-interface=ether1 filter-ip-address=1.1.1.1/32', $this->return_file_contents($devicerecord['commands'][5189]));
        $this->assertStringContainsString('11     pppoe-out1                          pppoe-out', $this->return_file_contents($devicerecord['commands'][5190]));

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
