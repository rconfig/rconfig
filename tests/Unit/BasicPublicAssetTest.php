<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicPublicAssetTest extends TestCase
{
    public function test_index_php()
    {
        $this->assertFileExists(public_path() . '/index.php');
    }

    public function test_assets_dir()
    {
        $this->assertDirectoryExists(public_path() . '/build/assets');
    }

    public function test_assets_dir_count()
    {
        $files = glob(public_path() . '/build/assets/*');
        $this->assertGreaterThan(250, $files);
    }

    public function test_manifest_json()
    {
        $this->assertFileExists(public_path() . '/build/manifest.json');
    }

    public function test_patternfly_icons_dir()
    {
        $this->assertFileExists(public_path() . '/fonts/vendor/@patternfly/patternfly/pficon/pficon.woff');
    }

    public function test_patternfly_fonts_dir()
    {
        $this->assertFileExists(public_path() . '/fonts/vendor/@patternfly/patternfly/overpass-mono-weboverpass-mono-bold.woff');
    }
}
