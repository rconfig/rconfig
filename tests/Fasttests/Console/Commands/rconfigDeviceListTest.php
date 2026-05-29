<?php

namespace Tests\Fasttests\Console\Commands;

use App\Console\Commands\rconfigDeviceList;
use App\Models\Device;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class rconfigDeviceListTest extends TestCase
{
    protected $user;
    protected $output;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_has_rconfig_device_list_command()
    {
        $this->assertTrue(class_exists(rconfigDeviceList::class));
    }

    public function test_list_devices_command()
    {
        $devices = Device::factory(20)->create();

        Artisan::call('rconfig:list-devices');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        $this->assertStringContainsString($arr[0], 'Results for Devices List:');
        $this->assertStringContainsString('1001', $arr[4]);
        $this->assertGreaterThan(20, count($arr));
    }
}
