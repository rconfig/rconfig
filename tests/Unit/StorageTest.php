<?php

namespace Tests\Unit;

use Tests\UnitTestCase;

class StorageTest extends UnitTestCase
{
    public function test_get_rconfig_appdir_paths()
    {
        $appDirPath = config('rConfig.app_dir_path');

        $this->assertEquals($appDirPath, rconfig_appdir_path());
        $this->assertEquals($appDirPath . '/storage', rconfig_appdir_storage_path());
    }
}
