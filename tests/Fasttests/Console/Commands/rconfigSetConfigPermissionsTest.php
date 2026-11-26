<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

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

test('it explains how to re-run when the current user cannot read the config folder', function () {
    // Owner only, because dropping to "nobody" keeps the test process's groups.
    chmod($this->dataPath, 0700);

    $exitCode = asUnprivilegedUser($this, fn (): int => Artisan::call('rconfig:set-config-permissions --dry-run'));
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Cannot read ' . $this->dataPath . ' as the current user.')
        ->and($output)->toContain('sudo -u ' . rconfig_web_user()[0] . ' php artisan rconfig:set-config-permissions --dry-run')
        ->and($output)->not->toContain('Scanning');
});

test('it reports a sub folder it cannot read instead of stopping', function () {
    $closedDir = $this->dataPath . 'Switches';
    File::makeDirectory($closedDir . '/s1', 0755, true, true);
    File::put($closedDir . '/s1/config.txt', "hostname s1\n");
    chmod($closedDir . '/s1/config.txt', 0444);
    chmod($closedDir, 0700);

    // Everything else stays open, so only the root-owned sub folder blocks the walk.
    $exitCode = asUnprivilegedUser($this, fn (): int => Artisan::call('rconfig:set-config-permissions --dry-run'));
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Could not read: ' . $closedDir)
        ->and($output)->toContain('1 path(s) could not be read or changed.')
        ->and($output)->toContain('sudo -u ' . rconfig_web_user()[0] . ' php artisan rconfig:set-config-permissions --dry-run')
        ->and($output)->toContain('would be changed');
});

/**
 * Runs the callback as an account that owns none of the fixture. Root drops to
 * "nobody" for the call, because root ignores file modes.
 */
function asUnprivilegedUser(TestCase $test, Closure $callback): mixed
{
    if (posix_geteuid() !== 0) {
        $test->markTestSkipped('Needs root, so the fixture can be owned by a different account than the one running the command.');
    }

    $nobody = posix_getpwnam('nobody');

    if ($nobody === false) {
        $test->markTestSkipped('Needs a "nobody" account to drop privileges to.');
    }

    posix_seteuid($nobody['uid']);

    try {
        return $callback();
    } finally {
        posix_seteuid(0);
    }
}

function modeOf(string $path): int
{
    clearstatcache(true, $path);

    return fileperms($path) & 0777;
}
