<?php

namespace Tests\Fasttests\Console\Commands;

use App\Console\Commands\rconfigClearAll;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class rconfigClearAllTest extends TestCase
{
    private string $appDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->appDir = sys_get_temp_dir() . '/rconfig-clear-all-' . uniqid();
        mkdir($this->appDir, 0755, true);
        Config::set('rConfig.app_dir_path', $this->appDir);
    }

    public function tearDown(): void
    {
        putenv('IS_DOCKER');

        $storage = $this->appDir . '/storage';
        if (is_dir($storage)) {
            chmod($storage, 0755);
            rmdir($storage);
        }
        if (is_dir($this->appDir)) {
            rmdir($this->appDir);
        }

        parent::tearDown();
    }

    public function test_it_has_rconfig_clear_all_command(): void
    {
        $this->assertTrue(class_exists(rconfigClearAll::class));
    }

    public function test_it_loosens_the_configured_storage_path_when_in_docker(): void
    {
        $storage = $this->appDir . '/storage';
        mkdir($storage, 0755, true);
        chmod($storage, 0755);
        putenv('IS_DOCKER=true');

        (new rconfigClearAll)->applyDockerStoragePermissions();

        $this->assertSame(0777, fileperms($storage) & 0777);
    }

    public function test_it_skips_silently_when_the_storage_path_is_absent(): void
    {
        putenv('IS_DOCKER=true');

        (new rconfigClearAll)->applyDockerStoragePermissions();

        $this->assertDirectoryDoesNotExist($this->appDir . '/storage');
    }

    public function test_it_does_nothing_when_not_running_in_docker(): void
    {
        $storage = $this->appDir . '/storage';
        mkdir($storage, 0755, true);
        chmod($storage, 0755);
        putenv('IS_DOCKER');

        (new rconfigClearAll)->applyDockerStoragePermissions();

        $this->assertSame(0755, fileperms($storage) & 0777);
    }
}
