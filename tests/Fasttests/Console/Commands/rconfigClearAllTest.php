<?php

use App\Console\Commands\rconfigClearAll;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

beforeEach(function () {
    $this->appDir = sys_get_temp_dir() . '/rconfig-clear-all-' . uniqid();
    mkdir($this->appDir, 0755, true);
    Config::set('rConfig.app_dir_path', $this->appDir);
});

afterEach(function () {
    putenv('IS_DOCKER');

    $storage = $this->appDir . '/storage';
    if (is_dir($storage)) {
        // Restore a sane mode first in case a test left it unusable.
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

test('it never leaves storage world writable in docker', function () {
    $storage = $this->appDir . '/storage';
    mkdir($storage, 0755, true);
    chmod($storage, 0777);
    $this->fakeHostFilesystem(osRelease: "ID=debian\n");
    putenv('IS_DOCKER=true');

    (new rconfigClearAll)->restoreDockerStoragePermissions();

    clearstatcache();
    expect(fileperms($storage) & 0002)->toBe(0)
        ->and(fileperms($storage) & 0777)->toBe(0775);
});

test('it hands the rebuilt caches back to the web user in docker', function () {
    foreach (['storage/framework/views', 'storage/logs', 'bootstrap/cache', 'storage/app/rconfig/data/Routers'] as $path) {
        mkdir($this->appDir . '/' . $path, 0755, true);
    }
    touch($this->appDir . '/bootstrap/cache/config.php');
    touch($this->appDir . '/storage/framework/views/compiled.php');
    $host = $this->fakeHostFilesystem(osRelease: "ID=debian\n");
    putenv('IS_DOCKER=true');

    (new rconfigClearAll)->restoreDockerStoragePermissions();

    $chowned = $host->chownedPaths();
    expect($chowned)->toContain(
        $this->appDir . '/storage',
        $this->appDir . '/bootstrap/cache',
        $this->appDir . '/bootstrap/cache/config.php',
        $this->appDir . '/storage/framework/views/compiled.php',
        $this->appDir . '/storage/logs',
    )
        ->and(array_unique(array_column($host->chowned, 'user')))->toBe(['www-data']);

    // Never walks the config data tree.
    expect(array_filter($chowned, fn (string $path): bool => str_contains($path, '/storage/app')))->toBeEmpty();
});

test('it skips silently when the storage path is absent', function () {
    $host = $this->fakeHostFilesystem(osRelease: "ID=debian\n");
    putenv('IS_DOCKER=true');

    (new rconfigClearAll)->restoreDockerStoragePermissions();

    $this->assertDirectoryDoesNotExist($this->appDir . '/storage');
    expect($host->chowned)->toBeEmpty()
        ->and($host->chmodded)->toBeEmpty();
});

test('it does nothing on bare metal', function (string|false $isDocker) {
    $storage = $this->appDir . '/storage';
    mkdir($storage . '/framework', 0755, true);
    chmod($storage, 0755);
    $host = $this->fakeHostFilesystem();
    $isDocker === false ? putenv('IS_DOCKER') : putenv("IS_DOCKER={$isDocker}");

    (new rconfigClearAll)->restoreDockerStoragePermissions();

    clearstatcache();
    expect(fileperms($storage) & 0777)->toBe(0755)
        ->and($host->chowned)->toBeEmpty()
        ->and($host->chmodded)->toBeEmpty();
})->with([
    'unset' => [false],
    'false' => ['false'],
]);

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

test('it restarts Horizon through horizon:terminate so Supervisor relaunches it', function () {
    Artisan::shouldReceive('call')->once()->with('horizon:terminate');

    (new rconfigClearAll)->restartHorizon();
});

test('it never restarts Supervisor or sniffs the OS release files', function () {
    $source = File::get(app_path('Console/Commands/rconfigClearAll.php'));

    expect($source)->not->toContain('supervisorctl')
        ->and($source)->not->toContain('systemctl')
        ->and($source)->not->toContain('/etc/redhat-release')
        ->and($source)->not->toContain('/etc/lsb-release');
});
