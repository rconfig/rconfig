<?php

use App\Console\Commands\rconfigClearAll;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->appDir = sys_get_temp_dir() . '/rconfig-clear-all-' . uniqid();
    mkdir($this->appDir, 0755, true);
    Config::set('rConfig.app_dir_path', $this->appDir);
});

afterEach(function () {
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

});

test('it has rconfig clear all command', function () {
    expect(class_exists(rconfigClearAll::class))->toBeTrue();
});

test('it loosens the configured storage path when in docker', function () {
    $storage = $this->appDir . '/storage';
    mkdir($storage, 0755, true);
    chmod($storage, 0755);
    putenv('IS_DOCKER=true');

    (new rconfigClearAll)->applyDockerStoragePermissions();

    expect(fileperms($storage) & 0777)->toBe(0777);
});

test('it skips silently when the storage path is absent', function () {
    putenv('IS_DOCKER=true');

    (new rconfigClearAll)->applyDockerStoragePermissions();

    $this->assertDirectoryDoesNotExist($this->appDir . '/storage');
});

test('it does nothing when not running in docker', function () {
    $storage = $this->appDir . '/storage';
    mkdir($storage, 0755, true);
    chmod($storage, 0755);
    putenv('IS_DOCKER');

    (new rconfigClearAll)->applyDockerStoragePermissions();

    expect(fileperms($storage) & 0777)->toBe(0755);
});

test('it detects configurations readable by other', function () {
    $config = writeStoredConfig($this->appDir, 'showrunningconfig_1200.txt', 0444);

    expect(fileperms($config) & 0777)->toBe(0444, 'Precondition: the fixture is world readable.');
    expect((new rconfigClearAll)->configPermissionsAreLoose())->toBeTrue();
});

test('it stays quiet when configurations are locked down', function () {
    writeStoredConfig($this->appDir, 'showrunningconfig_1200.txt', 0440);

    expect((new rconfigClearAll)->configPermissionsAreLoose())->toBeFalse();
});

test('it ignores files that are not stored configurations', function () {
    writeStoredConfig($this->appDir, '.gitignore', 0644);

    expect((new rconfigClearAll)->configPermissionsAreLoose())->toBeFalse();
});

test('it stays quiet when no configurations exist', function () {
    expect((new rconfigClearAll)->configPermissionsAreLoose())->toBeFalse();
});

/**
 * Writes a file into the configured config data tree and returns its path.
 */
function writeStoredConfig(string $appDir, string $filename, int $mode): string
{
    $directory = $appDir . '/storage/app/rconfig/data/Routers/r1/2026/Aug/01';
    mkdir($directory, 0750, true);

    $path = $directory . '/' . $filename;
    file_put_contents($path, "hostname r1\n");
    chmod($path, $mode);

    return $path;
}
