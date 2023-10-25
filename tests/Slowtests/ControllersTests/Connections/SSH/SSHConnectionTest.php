<?php

namespace Tests\Slowtests\ControllersTests\Connections\SSH;

use App\CustomClasses\DeviceRecordPrepare;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Models\Device;
use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SSHConnectionTest extends TestCase
{
    protected $user;

    protected $device3;

    protected $device4;

    protected $device6;

    protected $device11;

    public function setUp(): void
    {
        parent::setUp();
        $this->device3 = Device::where('id', 1003)->first();
        $this->device4 = Device::where('id', 1004)->first();
        $this->device6 = Device::where('id', 1006)->first();
        $this->device11 = Device::where('id', 1011)->first();

        // check if 192.168.1.170 is reachable
        $dev_cisco_ip = '192.168.1.170';
        $pingresult = exec("ping -c 1 -W 1 $dev_cisco_ip", $outcome, $status);

        if (str_contains($outcome[3], '0 received')) {
            $this->assertTrue(false);
            $this->markTestSkipped('Router is not reachable');
        }
    }

    /** @test */
    public function device3_was_found()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();

        $this->assertEquals(1003, $devicerecord['id']);
        $this->assertEquals('router3', $devicerecord['device_name']);
    }

    /** @test */
    public function device3_has_commands()
    {
        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();

        $this->assertGreaterThan(0, count($devicerecord['commands']));
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function full_SSH_download_from_device3_direct_from_classes()
    {
        //start timer
        $start = microtime(true);

        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        //end timer
        $time = microtime(true) - $start;
        $this->assertLessThan(3, $time);

        $this->assertGreaterThan(0, count($configsArray));
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function check_device_can_override_connection_template_port_but_fails_connection()
    {
        //start timer
        $start = microtime(true);

        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
        $devicerecord['device_port_override'] = 2222;
        $this->assertEquals(2222, $devicerecord['device_port_override']);

        $connectionObj = new MainConnectionManager($devicerecord, 0);

        $configsArray = $connectionObj->setupConnectAndReturnOutput();
        $this->assertEquals('There was an authentication or connection issue with router3', $configsArray['failure']);

        //end timer
        $time = microtime(true) - $start;
        $this->assertLessThan(3, $time);
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function check_device_can_override_connection_template_port_but_passes_connection()
    {
        //start timer
        $start = microtime(true);

        $devicerecord = (new DeviceRecordPrepare($this->device3))->DeviceRecordToArray();
        $devicerecord['device_port_override'] = 22;
        $this->assertEquals(22, $devicerecord['device_port_override']);

        $connectionObj = new MainConnectionManager($devicerecord, 0);

        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        //end timer
        $time = microtime(true) - $start;
        $this->assertLessThan(3, $time);

        $this->assertGreaterThan(0, count($configsArray));
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function full_SSH_download_from_device4_direct_from_classes()
    {
        //start timer
        $start = microtime(true);
        $devicerecord = (new DeviceRecordPrepare($this->device4))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        //end timer
        $time = microtime(true) - $start;
        $this->assertLessThan(3, $time);
        $this->assertGreaterThan(0, count($configsArray));
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function established_connection_has_options_params_if_they_are_set_in_the_template()
    {
        //start timer
        $start = microtime(true);
        $template = \App\Models\Template::factory()->create([
            'id' => 2000,
            'fileName' => '/app/rconfig/templates/fake_template_with_options.yml',
            'templateName' => 'Fake Template with Options Set',
            'description' => 'Fake Template with Options Set for testing purposes',
        ]);
        if (file_exists(base_path('tests/storage/fake_template_with_options.yml'))) {
            $this->assertTrue(true);
        }
        if (!copy(base_path('tests/storage/fake_template_with_options.yml'), storage_path() . '/app/rconfig/templates//fake_template_with_options.yml')) {
            $this->assertTrue(false);
            // echo "failed to copy $file...\n";
        }
        if (file_exists(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml')) {
            $this->assertTrue(true);
        }

        $devicerecord = (new DeviceRecordPrepare($this->device6))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($devicerecord, 0);
        $result = $connectionObj->getAllConnectionParamsArray();

        //end timer
        $time = microtime(true) - $start;
        $this->assertLessThan(3, $time);
        $this->assertIsObject($result);
        $this->assertEquals($result->options['AnsiHost'], 'yes');
        $this->assertEquals($result->options['setWindowSize'][0], 240);

        unlink(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml');
        if (!file_exists(storage_path() . '/app/rconfig/templates/fake_template_with_options.yml')) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function full_ssh_download_and_file_exists_check_from_command_no_enable_SSH_template_and_regex_prompt()
    {
        $start = microtime(true);
        $this->device3['device_main_prompt'] = '.*router.*#';
        $this->assertStringContainsString('.*router.*#', $this->device3['device_main_prompt']);

        Artisan::call('rconfig:download-device 1003');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device3, $match[0]));
            }
        }
        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1003');
        $this->assertStringContainsString($arr[1], 'Start device download for router3 ID:1003');
        $this->assertDatabaseHas('devices', [
            'id' => 1003,
            'status' => 1,
        ]);

        $this->device3->device_main_prompt = 'r1#';
        $this->assertStringContainsString('r1#', $this->device3['device_main_prompt']);
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function full_ssh_download_and_file_exists_check_from_command_no_enable_SSH_template()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1003');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device3, $match[0]));
            }
        }
        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1003');
        $this->assertStringContainsString($arr[1], 'Start device download for router3 ID:1003');
        $this->assertDatabaseHas('devices', [
            'id' => 1003,
            'status' => 1,
        ]);
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function V6_full_ssh_download_and_file_exists_check_from_command_no_enable_SSH_template()
    {
        $start = microtime(true);

        Artisan::call('rconfig:download-device 1010');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device3, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1010');
        $this->assertStringContainsString($arr[1], 'Start device download for router1v6 ID:1010');
        $this->assertDatabaseHas('devices', [
            'id' => 1003,
            'status' => 1,
        ]);
    }

    /**
     * @test
     *
     * @group slow-tests
     */
    public function full_ssh_download_and_file_exists_check_from_device4_from_command_enable_SSH_template()
    {
        $start = microtime(true);
        Artisan::call('rconfig:download-device 1004');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $time = microtime(true) - $start;
        $this->assertLessThan(5, $time);

        foreach ($arr as $line) {
            preg_match('/"([^"]+)"/', $line, $match); // get the command from between the quotes in the returned output
            if (!empty($match)) {
                $this->assertTrue($this->downloaded_file_exists_on_disk($this->device4, $match[0]));
            }
        }

        $this->assertGreaterThan(0, count($arr));
        $this->assertStringContainsString($arr[0], 'Start rconfig:download-device IDs:1004');
        $this->assertStringContainsString($arr[1], 'Start device download for router4 ID:1004');
        $this->assertDatabaseHas('devices', [
            'id' => 1004,
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

    private function setup_extra_devices()
    {
        $dev_cisco_ip = '192.168.1.170';

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
    private function remove_extra_devices()
    {
        Device::find(10031)->delete();
        Device::find(10032)->delete();
        DB::table('category_device')->where('device_id', 10031)->delete();
        DB::table('device_vendor')->where('device_id', 10031)->delete();
        DB::table('category_device')->where('device_id', 10032)->delete();
        DB::table('device_vendor')->where('device_id', 10032)->delete();
    }
}
