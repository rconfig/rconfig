<?php

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get rconfig appdir paths', function () {
    // Defaults to the prod path, but honours the APP_DIR_PATH env override (e.g. in dev)
    $expectedPath = config('rConfig.app_dir_path');

    expect(rconfig_appdir_path())->toEqual($expectedPath);
    expect(rconfig_appdir_storage_path())->toEqual($expectedPath . '/storage');
});
