<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicStorageAssetTest extends TestCase
{
    public function test_data_dir_is_present()
    {
        $this->assertDirectoryExists(config_data_path());
    }
}
