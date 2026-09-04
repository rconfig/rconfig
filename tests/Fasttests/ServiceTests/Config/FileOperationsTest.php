<?php

use App\Services\Config\FileOperations;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->categoryName = 'TestCat';
    $this->deviceName = 'TestDevice';

    $this->dataBaseDir = storage_path('app/rconfig/tempconfigs/' . uniqid('fileops_', true) . '/');
    File::makeDirectory($this->dataBaseDir, 0750, true, true);
});

afterEach(function () {
    $this->rollBackTransaction();

    if (isset($this->dataBaseDir) && File::exists($this->dataBaseDir)) {
        File::deleteDirectory($this->dataBaseDir);
    }

});

test('saved config file is not readable by other', function () {
    $result = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['hostname r1', 'snmp-server community s3cr3t RO']);

    expect($result['download_status'])->toBeTrue();
    expect($result['filepath'])->toBeFile();

    expect(fileOpsModeOf($result['filepath']))->toBe(0440, 'Stored config files must be 0440. A world readable config exposes device secrets to any local account.');

    expect(fileperms($result['filepath']) & 0007)->toBe(0, 'Stored config files must have no "other" permission bits.');
});

test('created directories are not traversable by other', function () {
    $result = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['hostname r1']);

    // A tight file mode is defeated by a traversable parent, so every level
    // of the category/device/year/month/day tree has to be checked.
    $directory = dirname($result['filepath']);

    while (rtrim($directory, '/') !== rtrim($this->dataBaseDir, '/')) {
        expect(fileOpsModeOf($directory))->toBe(0750, "Config directory must be 0750, got a looser mode on: {$directory}");

        expect(fileperms($directory) & 0007)->toBe(0, "Config directory must not be traversable or readable by other: {$directory}");

        $directory = dirname($directory);
    }
});

test('file contents are written correctly', function () {
    $result = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['line one', 'line two']);

    expect(file_get_contents($result['filepath']))->toBe('line one' . PHP_EOL . 'line two');
    expect($result['filesize'])->toBe(strlen('line one' . PHP_EOL . 'line two'));
});

test('existing read only config can be overwritten by a later download', function () {
    $first = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['first download']);
    expect(fileOpsModeOf($first['filepath']))->toBe(0440);

    // The second download reuses the same path (same command, same minute).
    // It must reopen the read only file, rewrite it, and re-lock it.
    $second = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['second download']);

    expect($second['filepath'])->toBe($first['filepath'], 'Expected the same target path for this assertion to be meaningful.');
    expect($second['download_status'])->toBeTrue();
    expect(file_get_contents($second['filepath']))->toBe('second download');
    expect(fileOpsModeOf($second['filepath']))->toBe(0440, 'The file must be returned to read only after being rewritten.');
});

test('write window grants no access to other', function () {
    $fileops = new FileOperations(
        'show running-config',
        $this->categoryName,
        $this->deviceName,
        1,
        $this->dataBaseDir,
        'cli'
    );

    $path = $fileops->createFile('show running-config');

    expect($path)->toBeFile();
    expect(fileOpsModeOf($path))->toBe(0660, 'The write window must be 0660, never 0666.');
    expect(fileperms($path) & 0007)->toBe(0, 'The write window must not expose the file to other.');
});

test('write window follows a tightened file mode', function () {
    config(['rConfig.config_file_mode' => 0400]);

    $fileops = new FileOperations(
        'show running-config',
        $this->categoryName,
        $this->deviceName,
        1,
        $this->dataBaseDir,
        'cli'
    );

    // An operator tightening the file mode to owner only must not have the
    // write window silently widen it back to the group.
    expect(fileOpsModeOf($fileops->createFile('show running-config')))->toBe(0600);
});

test('configured modes are honoured', function () {
    config(['rConfig.config_file_mode' => 0400, 'rConfig.config_dir_mode' => 0700]);

    $result = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['hostname r1']);

    expect(fileOpsModeOf($result['filepath']))->toBe(0400);
    expect(fileOpsModeOf(dirname($result['filepath'])))->toBe(0700);
});

test('directory mode is not weakened by the process umask', function () {
    // mkdir()'s mode argument is masked by the umask, so a permissive umask
    // used to be enough to leave the tree looser than intended.
    $originalUmask = umask(0);

    try {
        $result = fileOpsSaveConfig($this->categoryName, $this->deviceName, $this->dataBaseDir, ['hostname r1']);

        expect(fileOpsModeOf(dirname($result['filepath'])))->toBe(0750);
        expect(fileOpsModeOf($result['filepath']))->toBe(0440);
    } finally {
        umask($originalUmask);
    }
});

/**
 * @param  array<int, string>  $lines
 * @return array{filepath: string, filename: string, download_status: bool, filesize: int}
 */
function fileOpsSaveConfig(string $categoryName, string $deviceName, string $dataBaseDir, array $lines): array
{
    $fileops = new FileOperations(
        'show running-config',
        $categoryName,
        $deviceName,
        1,
        $dataBaseDir,
        'cli'
    );

    return $fileops->saveFile($lines);
}

function fileOpsModeOf(string $path): int
{
    clearstatcache(true, $path);

    return fileperms($path) & 0777;
}
