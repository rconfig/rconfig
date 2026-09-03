<?php

namespace Tests\Unit;

use Tests\UnitTestCase;

class BasicStorageAssetTest extends UnitTestCase
{
    public function test_data_dir_is_present()
    {
        $this->assertDirectoryExists(config_data_path());
    }
}
