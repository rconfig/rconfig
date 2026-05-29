<?php

namespace Tests\Fasttests\OtherTests;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StorageTest extends TestCase
{
    use WithFaker;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_rconfig_appdir_paths()
    {
        // Defaults to the prod path, but honours the APP_DIR_PATH env override (e.g. in dev)
        $expectedPath = config('rConfig.app_dir_path');

        $this->assertEquals($expectedPath, rconfig_appdir_path());
        $this->assertEquals($expectedPath . '/storage', rconfig_appdir_storage_path());
    }
}
