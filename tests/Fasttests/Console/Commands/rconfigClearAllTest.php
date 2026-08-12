<?php

namespace Tests\Fasttests\Console\Commands;

use App\Console\Commands\rconfigClearAll;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
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
            // The docker test loosens this to 0777; restore a sane mode first.
            chmod($storage, 0755);
        }
        if (is_dir($this->appDir)) {
            // Recursive: some tests build a nested config data tree here, which
            // rmdir cannot remove.
            File::deleteDirectory($this->appDir);
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

    public function test_it_detects_configurations_readable_by_other(): void
    {
        $config = $this->writeStoredConfig('showrunningconfig_1200.txt', 0444);

        $this->assertSame(0444, fileperms($config) & 0777, 'Precondition: the fixture is world readable.');
        $this->assertTrue((new rconfigClearAll)->configPermissionsAreLoose());
    }

    public function test_it_stays_quiet_when_configurations_are_locked_down(): void
    {
        $this->writeStoredConfig('showrunningconfig_1200.txt', 0440);

        $this->assertFalse((new rconfigClearAll)->configPermissionsAreLoose());
    }

    /**
     * A .gitignore ships in the data directory at 0644. It is not a stored
     * configuration, so it must not trigger the warning on its own.
     */
    public function test_it_ignores_files_that_are_not_stored_configurations(): void
    {
        $this->writeStoredConfig('.gitignore', 0644);

        $this->assertFalse((new rconfigClearAll)->configPermissionsAreLoose());
    }

    public function test_it_stays_quiet_when_no_configurations_exist(): void
    {
        $this->assertFalse((new rconfigClearAll)->configPermissionsAreLoose());
    }

    /**
     * Writes a file into the configured config data tree and returns its path.
     */
    private function writeStoredConfig(string $filename, int $mode): string
    {
        $directory = $this->appDir . '/storage/app/rconfig/data/Routers/r1/2026/Aug/01';
        mkdir($directory, 0750, true);

        $path = $directory . '/' . $filename;
        file_put_contents($path, "hostname r1\n");
        chmod($path, $mode);

        return $path;
    }
}
