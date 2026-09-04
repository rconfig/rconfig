<?php

use App\Models\Command;
use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\Setting;
use App\Services\ConfigHistory\ConfigHistoryManager;
use App\Services\Templates\CompareExclusionTemplateService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();

    $this->command = 'show run versioning test';

    // Ensure the comparison settings + default exclusion file are present.
    (new CompareExclusionTemplateService)->installDefaultTemplate();

    Command::firstOrCreate(['command' => $this->command]);

    $this->workDir = storage_path('app/rconfig/tempconfigs/' . uniqid('vtest_', true) . '/');
    File::makeDirectory($this->workDir, 0777, true, true);
});

afterEach(function () {
    $this->rollBackTransaction();
    File::deleteDirectory($this->workDir);
    File::delete(File::glob(tmp_dir() . '/*.txt'));
});

function versionCompareWriteFile(string $workDir, string $name, string $content): string
{
    $path = $workDir . $name;
    File::put($path, $content);

    return $path;
}

function versionCompareMakeConfig(string $command, string $filePath, ?int $version, int $latest): Config
{
    return Config::create([
        'device_id' => 987654,
        'device_name' => 'versiontest-router',
        'device_category' => 'Routers',
        'command' => $command,
        'type' => 'device_download',
        'download_status' => 1,
        'config_location' => $filePath,
        'config_filename' => basename($filePath),
        'config_filesize' => filesize($filePath),
        'config_version' => $version,
        'latest_version' => $latest,
    ]);
}

test('first config version is set to one with no change record', function () {
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'v1.txt', "hostname r1\ninterface g0/0\n"), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    expect($current->fresh()->config_version)->toBe(1);
    expect(ConfigChange::where('current_config_id', $current->id)->count())->toBe(0);
});

test('identical config reuses previous version and creates no change', function () {
    $content = "hostname r1\ninterface g0/0\n description uplink\n";
    $previous = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'prev.txt', $content), 1, 0);
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'curr.txt', $content), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    expect($current->fresh()->config_version)->toBe(1);
    expect(ConfigChange::where('current_config_id', $current->id)->count())->toBe(0);
});

test('changed config bumps version and records a change', function () {
    $previous = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'curr.txt', "hostname r1\ninterface g0/0\n ip address 10.0.0.1 255.255.255.0\n"), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    expect($current->fresh()->config_version)->toBe(2);

    $change = ConfigChange::where('current_config_id', $current->id)->first();
    expect($change)->not->toBeNull();
    expect($change->previous_config_id)->toBe($previous->id);
    expect($change->config_version)->toBe(2);
    expect($change->config_change_type)->toBe('added');
    expect($change->config_diff)->not->toBeEmpty();
});

test('zero byte current config does not change version or record', function () {
    versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'prev.txt', "hostname r1\ninterface g0/0\n"), 1, 0);

    // Current file is empty (0 bytes) -> treated as invalid, no version assigned.
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'curr.txt', ''), null, 1);

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    expect($current->fresh()->config_version)->toBeNull();
    expect(ConfigChange::where('current_config_id', $current->id)->count())->toBe(0);
});

test('unknown command does not version the config', function () {
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'v1.txt', "hostname r1\n"), null, 1);

    // No Command record exists for this name, so versioning is skipped.
    $result = (new ConfigHistoryManager)->handleNewDownloadedConfig($current, 'command that does not exist');

    expect($result)->toBeFalse();
    expect($current->fresh()->config_version)->toBeNull();
    expect(ConfigChange::where('current_config_id', $current->id)->count())->toBe(0);
});

test('change only on excluded line creates no change record', function () {
    // The default exclusion template drops "Last configuration change" lines.
    $previous = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'prev.txt', "! Last configuration change at 10:00\nhostname r1\n"), 1, 0);
    $current = versionCompareMakeConfig($this->command, versionCompareWriteFile($this->workDir, 'curr.txt', "! Last configuration change at 11:30\nhostname r1\n"), null, 1);

    // Sanity: the exclusion file is installed.
    expect(Setting::find(1)->config_compare_exclusion_file)->not->toBeEmpty();

    (new ConfigHistoryManager)->handleNewDownloadedConfig($current, $this->command);

    expect($current->fresh()->config_version)->toBe(1);
    expect(ConfigChange::where('current_config_id', $current->id)->count())->toBe(0);
});
