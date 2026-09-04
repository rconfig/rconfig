<?php

use App\Console\Commands\rconfigTagList;
use Illuminate\Support\Facades\Artisan;

test('it has rconfig tag list command', function () {
    expect(class_exists(rconfigTagList::class))->toBeTrue();
});

test('list tags command', function () {
    Artisan::call('rconfig:list-tags');
    $result = Artisan::output();
    $arr = explode("\n", $result);

    $this->assertStringContainsString($arr[0], 'Results for Tags List:');
    $this->assertStringContainsString('Routers', $arr[4]);
    $this->assertStringContainsString('Switches', $arr[5]);
});
