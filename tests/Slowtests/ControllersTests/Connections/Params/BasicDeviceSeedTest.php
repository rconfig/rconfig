<?php

namespace Tests\Slowtests\ControllersTests\Connections\Params;

use App\Models\Category;
use App\Models\Device;
use Tests\TestCase;

class BasicDeviceSeedTest extends TestCase
{
    protected $user;

    protected $device;

    public function setUp(): void
    {
        parent::setUp();
        $this->device = Device::where('id', 1001)->first();
    }

    public function test_device_1001_was_seeded_to_db()
    {
        $this->assertDatabaseHas('devices', [
            'id' => 1001,
            'device_name' => 'router1',
            'device_ip' => '10.1.1.170',
        ]);
    }

    public function test_device_1005_was_seeded_to_db()
    {
        $this->assertDatabaseHas('devices', [
            'id' => 1005,
            'device_name' => 'router5',
            'device_ip' => '192.169.1.1',
        ]);
    }

    public function test_category_exists_for_this_device()
    {
        $category = Category::where('id', $this->device['device_category_id'])->first();
        $category = $category->toArray();
        $this->assertStringContainsString($category['categoryName'], 'Routers'); // because seeded device ID 1000 has CAT id of 1.
    }
}
