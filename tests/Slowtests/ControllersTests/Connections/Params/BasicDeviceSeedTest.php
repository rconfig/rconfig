<?php

use App\Models\Category;
use App\Models\Device;

beforeEach(function () {
    $this->device = Device::where('id', 1001)->first();
});

test('device 1001 was seeded to db', function () {
    $this->assertDatabaseHas('devices', [
        'id' => 1001,
        'device_name' => 'router1',
        'device_ip' => '10.1.1.170',
    ]);
});

test('device 1005 was seeded to db', function () {
    $this->assertDatabaseHas('devices', [
        'id' => 1005,
        'device_name' => 'router5',
        'device_ip' => '192.169.1.1',
    ]);
});

test('category exists for this device', function () {
    $category = Category::where('id', $this->device['device_category_id'])->first();
    $category = $category->toArray();
    $this->assertStringContainsString($category['categoryName'], 'Routers');
    // because seeded device ID 1000 has CAT id of 1.
});
