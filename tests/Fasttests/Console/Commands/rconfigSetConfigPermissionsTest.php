<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
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
});

afterEach(function () {
    if (isset($this->appDirPath) && File::exists($this->appDirPath)) {
        File::deleteDirectory($this->appDirPath);
    }

});

test('it tightens existing world readable configs', function () {
    expect(modeOf($this->configFile))->toBe(0444, 'Precondition: the fixture starts world readable.');

    $this->artisan('rconfig:set-config-permissions')
        ->assertExitCode(0);

    expect(modeOf($this->configFile))->toBe(0440);
    expect(modeOf($this->configDir))->toBe(0750);
    expect(fileperms($this->configFile) & 0007)->toBe(0);
});

test('it tightens every level of the directory tree', function () {
    $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

    $directory = $this->configDir;

    while (rtrim($directory, '/') !== rtrim($this->dataPath, '/')) {
        expect(modeOf($directory))->toBe(0750, "Expected 0750 on: {$directory}");
        $directory = dirname($directory);
    }

    // The data directory itself must be covered, not just its contents.
    expect(modeOf($this->dataPath))->toBe(0750);
});

test('dry run reports without changing anything', function () {
    $this->artisan('rconfig:set-config-permissions --dry-run')
        ->expectsOutputToContain('[dry run]')
        ->assertExitCode(0);

    expect(modeOf($this->configFile))->toBe(0444, 'A dry run must not alter permissions.');
    expect(modeOf($this->configDir))->toBe(0755, 'A dry run must not alter permissions.');
});

test('it preserves file contents', function () {
    $original = file_get_contents($this->configFile);

    $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

    expect(file_get_contents($this->configFile))->toBe($original);
});

test('it honours configured modes', function () {
    config(['rConfig.config_file_mode' => 0400, 'rConfig.config_dir_mode' => 0700]);

    $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

    expect(modeOf($this->configFile))->toBe(0400);
    expect(modeOf($this->configDir))->toBe(0700);
});

test('it tightens the temp dir and its contents', function () {
    $tempDir = $this->appDirPath . '/storage/app/rconfig/tempdir/';
    File::makeDirectory($tempDir, 0755, true, true);
    $tempFile = $tempDir . 'temp_abc123.txt';
    File::put($tempFile, "hostname r1\n");
    chmod($tempDir, 0777);
    chmod($tempFile, 0644);

    $this->artisan('rconfig:set-config-permissions')->assertExitCode(0);

    expect(modeOf($tempDir))->toBe(0750);
    expect(modeOf($tempFile))->toBe(0440);
    expect(fileperms($tempFile) & 0007)->toBe(0);
});

test('it exits cleanly when the data directory is absent', function () {
    File::deleteDirectory($this->appDirPath);

    $this->artisan('rconfig:set-config-permissions')
        ->expectsOutputToContain('nothing to do')
        ->assertExitCode(0);
});

function modeOf(string $path): int
{
    clearstatcache(true, $path);

    return fileperms($path) & 0777;
}
