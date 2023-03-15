<?php

namespace Tests\Fasttests\OtherTests;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StorageTest extends TestCase
{
    use WithFaker;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function get_rconfig_appdir_paths()
    {
        $this->assertEquals(rconfig_appdir_path(), '/var/www/html/rconfig6'); // in dev
        $this->assertEquals(rconfig_appdir_storage_path(), '/var/www/html/rconfig6/storage'); // in dev
    }
}
