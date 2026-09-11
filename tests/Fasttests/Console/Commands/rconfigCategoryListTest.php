<?php

use App\Console\Commands\rconfigCatList;
use Illuminate\Support\Facades\Artisan;

test('it has rconfig category list command', function () {
    expect(class_exists(rconfigCatList::class))->toBeTrue();
});

test('list category command', function () {
    Artisan::call('rconfig:list-categories');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Results for Categories List:');
    $this->assertStringContainsString('Routers', $arr[4]);
    $this->assertStringContainsString('Switches', $arr[5]);
});
