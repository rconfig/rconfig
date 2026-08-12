<?php

namespace Tests\Fasttests\Console\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Tightening FileOperations only affects newly written configs, so upgrading
 * installs rely on this command to remediate configs already on disk.
 */
class rconfigSetConfigPermissionsTest extends TestCase
{
    private string $appDirPath;
    private string $dataPath;
    private string $configFile;
    private string $configDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->appDirPath = storage_path('app/rconfig/tempconfigs/' . uniqid('fixperms_', true));
        $this->dataPath = $this->appDirPath . '/storage/app/rconfig/data/';
        $this->configDir = $this->dataPath . 'Routers/r1/2026/Aug/01';

        File::makeDirectory($this->configDir, 0755, true, true);

        $this->configFile = $this->configDir . '/showrunningconfig_1200.txt';
        File::put($this->configFile, "hostname r1\nsnmp-server community s3cr3t RO\n");

        // Reproduce the pre-fix state this command exists to clean up.
        chmod($this->configFile, 0444);
        chmod($this->configDir, 0755);

        config(['rConfig.app_dir_path' => $this->appDirPath]);
    }

    public function tearDown(): void
    {
        if (isset($this->appDirPath) && File::exists($this->appDirPath)) {
            File::deleteDirectory($this->appDirPath);
        }

        parent::tearDown();
    }

    public function test_it_tightens_existing_world_readable_configs(): void
    {
        $this->assertSame(0444, $this->modeOf($this->configFile), 'Precondition: the fixture starts world readable.');

        $this->artisan('rconfig:set-config-permissions')
            ->assertExitCode(0);

        $this->assertSame(0440, $this->modeOf($this->configFile));
        $this->assertSame(0750, $this->modeOf($this->configDir));
        $this->assertSame(0, fileperms($this->configFile) & 0007);
    }

    public function test_it_tightens_every_level_of_the_directory_tree(): void
    {
        $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

        $directory = $this->configDir;

        while (rtrim($directory, '/') !== rtrim($this->dataPath, '/')) {
            $this->assertSame(0750, $this->modeOf($directory), "Expected 0750 on: {$directory}");
            $directory = dirname($directory);
        }

        // The data directory itself must be covered, not just its contents.
        $this->assertSame(0750, $this->modeOf($this->dataPath));
    }

    public function test_dry_run_reports_without_changing_anything(): void
    {
        $this->artisan('rconfig:set-config-permissions --dry-run')
            ->expectsOutputToContain('[dry run]')
            ->assertExitCode(0);

        $this->assertSame(0444, $this->modeOf($this->configFile), 'A dry run must not alter permissions.');
        $this->assertSame(0755, $this->modeOf($this->configDir), 'A dry run must not alter permissions.');
    }

    public function test_it_preserves_file_contents(): void
    {
        $original = file_get_contents($this->configFile);

        $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

        $this->assertSame($original, file_get_contents($this->configFile));
    }

    public function test_it_honours_configured_modes(): void
    {
        config(['rConfig.config_file_mode' => 0400, 'rConfig.config_dir_mode' => 0700]);

        $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

        $this->assertSame(0400, $this->modeOf($this->configFile));
        $this->assertSame(0700, $this->modeOf($this->configDir));
    }

    /**
     * The temp dir is created on demand, so an upgrading install already has it
     * at a loose mode and tmp_dir() will never tighten it. It holds cleaned
     * copies of config content, so the command has to cover it as well.
     */
    public function test_it_tightens_the_temp_dir_and_its_contents(): void
    {
        $tempDir = $this->appDirPath . '/storage/app/rconfig/tempdir/';
        File::makeDirectory($tempDir, 0755, true, true);
        $tempFile = $tempDir . 'temp_abc123.txt';
        File::put($tempFile, "hostname r1\n");
        chmod($tempDir, 0777);
        chmod($tempFile, 0644);

        $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

        $this->assertSame(0750, $this->modeOf($tempDir));
        $this->assertSame(0440, $this->modeOf($tempFile));
        $this->assertSame(0, fileperms($tempFile) & 0007);
    }

    public function test_it_exits_cleanly_when_the_data_directory_is_absent(): void
    {
        File::deleteDirectory($this->appDirPath);

        $this->artisan('rconfig:set-config-permissions')
            ->expectsOutputToContain('nothing to do')
            ->assertExitCode(0);
    }

    private function modeOf(string $path): int
    {
        clearstatcache(true, $path);

        return fileperms($path) & 0777;
    }
}
