<?php

use App\Console\Commands\rconfigDeviceList;
use App\Models\Device;
use Illuminate\Support\Facades\Artisan;

test('it has rconfig device list command', function () {
    expect(class_exists(rconfigDeviceList::class))->toBeTrue();
});

test('list devices command', function () {
    $devices = Device::factory(20)->create();

    Artisan::call('rconfig:list-devices');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Results for Devices List:');
    expect(deviceListSearchPartial((string) $devices->first()->id, $arr))->toBeTrue();
    expect(count($arr))->toBeGreaterThan(20);
});

function deviceListSearchPartial($keyword, $arr)
{
    foreach ($arr as $index => $string) {
        if (strpos($string, $keyword) !== false) {
            return true;
        }
    }
}
