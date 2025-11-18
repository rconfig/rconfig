<?php

namespace Tests\Unit;

use Tests\TestCase;

class StorageTest extends TestCase
{
    public function test_get_rconfig_appdir_paths()
    {
        $this->assertEquals(rconfig_appdir_path(), '/var/www/html/rconfig'); // in dev
        $this->assertEquals(rconfig_appdir_storage_path(), '/var/www/html/rconfig/storage'); // in dev
    }
}
