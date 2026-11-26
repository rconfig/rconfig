<?php

use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\ConfigSummary;
use App\Models\Device;
use App\Models\SettingsPurge;
use App\Services\DataPurge\ConfigRetentionService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->beginTransaction();
    Config::query()->delete();
    ConfigChange::query()->delete();
    ConfigSummary::query()->delete();
    SettingsPurge::query()->delete();

    $this->device = Device::factory()->create();
    $this->tempDir = sys_get_temp_dir() . '/rconfig-retention-' . uniqid();
    File::makeDirectory($this->tempDir, 0755, true);
});

afterEach(function () {
    File::deleteDirectory($this->tempDir);
    $this->rollBackTransaction();
});

function configsSetting(int $days = 30, array $options = []): SettingsPurge
{
    return SettingsPurge::factory()->forTarget('configs')->withRetentionDays($days)->create([
        'options' => array_merge([
            'batch_size' => 2,
            'cleanup_frequency' => 'daily',
            'enabled' => true,
            'min_versions_per_device_command' => 1,
        ], $options),
    ]);
}

function retentionConfig(int $deviceId, int $version, int $daysAgo, array $overrides = []): Config
{
    return Config::factory()->create(array_merge([
        'device_id' => $deviceId,
        'command' => 'show run',
        'download_status' => 1,
        'config_version' => $version,
        'latest_version' => 0,
        'config_location' => null,
        'end_time' => now()->subDays($daysAgo),
    ], $overrides));
}

test('old versions are deleted and the latest version is always kept', function () {
    $old = retentionConfig($this->device->id, 1, 200);
    $alsoOld = retentionConfig($this->device->id, 2, 100);
    $latest = retentionConfig($this->device->id, 3, 90, ['latest_version' => 1]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::pluck('id')->all())->toBe([$latest->id])
        ->and($report->totalConfigsConsidered)->toBe(3)
        ->and($report->totalConfigsDeleted)->toBe(2)
        ->and($report->configIdsToDelete)->toBe([$old->id, $alsoOld->id]);
});

test('the newest minimum versions are kept whatever their age', function () {
    retentionConfig($this->device->id, 1, 400);
    retentionConfig($this->device->id, 2, 300);
    retentionConfig($this->device->id, 3, 200);
    retentionConfig($this->device->id, 4, 100, ['latest_version' => 1]);

    app(ConfigRetentionService::class)->runFromSettings(configsSetting(30, ['min_versions_per_device_command' => 3]));

    expect(Config::orderBy('config_version')->pluck('config_version')->all())->toBe([2, 3, 4]);
});

test('retention is applied per device and command', function () {
    $other = Device::factory()->create();

    retentionConfig($this->device->id, 1, 100);
    retentionConfig($this->device->id, 2, 90, ['latest_version' => 1]);
    retentionConfig($this->device->id, 1, 100, ['command' => 'show version', 'latest_version' => 1]);
    retentionConfig($other->id, 1, 100, ['latest_version' => 1]);

    app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::count())->toBe(3)
        ->and(Config::where('device_id', $this->device->id)->where('command', 'show run')->pluck('config_version')->all())->toBe([2]);
});

test('configs newer than the cutoff and configs with no end time are kept', function () {
    retentionConfig($this->device->id, 1, 100, ['end_time' => null]);
    retentionConfig($this->device->id, 2, 5);
    retentionConfig($this->device->id, 3, 1, ['latest_version' => 1]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::count())->toBe(3)
        ->and($report->totalConfigsToDelete)->toBe(0);
});

test('the highest version is protected when no row is flagged latest', function () {
    retentionConfig($this->device->id, 1, 300);
    $highest = retentionConfig($this->device->id, 2, 200);

    app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::pluck('id')->all())->toBe([$highest->id]);
});

test('change rows that reference a deleted config are removed and the rest are kept', function () {
    $v1 = retentionConfig($this->device->id, 1, 300);
    $v2 = retentionConfig($this->device->id, 2, 200);
    $v3 = retentionConfig($this->device->id, 3, 100);
    $v4 = retentionConfig($this->device->id, 4, 90, ['latest_version' => 1]);

    $asCurrent = ConfigChange::factory()->create(['current_config_id' => $v2->id, 'previous_config_id' => $v1->id]);
    $asPrevious = ConfigChange::factory()->create(['current_config_id' => $v3->id, 'previous_config_id' => $v2->id]);
    $untouched = ConfigChange::factory()->create(['current_config_id' => $v4->id, 'previous_config_id' => $v3->id]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting(30, ['min_versions_per_device_command' => 2]));

    expect(Config::orderBy('id')->pluck('id')->all())->toBe([$v3->id, $v4->id])
        ->and(ConfigChange::toBase()->pluck('id')->all())->toBe([$untouched->id])
        ->and($report->totalConfigChangesDeleted)->toBe(2)
        ->and($report->configChangeIdsToDelete)->toEqualCanonicalizing([$asCurrent->id, $asPrevious->id]);
});

test('failed downloads use their own cutoff and have no minimum versions protection', function () {
    retentionConfig($this->device->id, 1, 5, ['latest_version' => 1]);
    $oldFailure = retentionConfig($this->device->id, 0, 40, ['download_status' => 0]);
    $recentFailure = retentionConfig($this->device->id, 0, 10, ['download_status' => 0]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting(365, ['non_downloaded_retention_days' => 30]));

    expect(Config::whereKey($oldFailure->id)->exists())->toBeFalse()
        ->and(Config::whereKey($recentFailure->id)->exists())->toBeTrue()
        ->and($report->totalNonDownloadedConfigsDeleted)->toBe(1);
});

test('failed downloads are left alone when no failed download retention is set', function () {
    retentionConfig($this->device->id, 0, 400, ['download_status' => 0]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::count())->toBe(1)
        ->and($report->totalNonDownloadedConfigsToDelete)->toBe(0);
});

test('configs of a disabled device are kept and orphaned configs are purged', function () {
    $disabled = Device::factory()->create(['status' => Device::STATUS_DISABLED]);
    $missingDeviceId = 99990001;

    retentionConfig($disabled->id, 1, 300);
    retentionConfig($disabled->id, 2, 200, ['latest_version' => 1]);
    retentionConfig($missingDeviceId, 1, 300);
    retentionConfig($missingDeviceId, 2, 200, ['latest_version' => 1]);

    app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::where('device_id', $disabled->id)->count())->toBe(2)
        ->and(Config::where('device_id', $missingDeviceId)->pluck('config_version')->all())->toBe([2]);
});

test('a dry run reports what would go and deletes nothing', function () {
    retentionConfig($this->device->id, 1, 300);
    $v2 = retentionConfig($this->device->id, 2, 200, ['latest_version' => 1]);
    ConfigChange::factory()->create(['current_config_id' => $v2->id, 'previous_config_id' => Config::min('id')]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting(), true);

    expect(Config::count())->toBe(2)
        ->and(ConfigChange::count())->toBe(1)
        ->and($report->totalConfigsToDelete)->toBe(1)
        ->and($report->totalConfigChangesToDelete)->toBe(1)
        ->and($report->totalRecordsDeleted())->toBe(0);
});

test('a disabled setting or zero retention does nothing', function (int $days, bool $enabled) {
    retentionConfig($this->device->id, 1, 300);
    retentionConfig($this->device->id, 2, 200, ['latest_version' => 1]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting($days, ['enabled' => $enabled]));

    expect(Config::count())->toBe(2)
        ->and($report->totalConfigsConsidered)->toBe(0);
})->with([
    'disabled' => [30, false],
    'zero retention' => [0, true],
]);

test('the files of deleted configs are removed from disk', function () {
    $oldFile = $this->tempDir . '/old.txt';
    $failedFile = $this->tempDir . '/failed.txt';
    $keptFile = $this->tempDir . '/kept.txt';
    File::put($oldFile, 'old');
    File::put($failedFile, '');
    File::put($keptFile, 'kept');

    retentionConfig($this->device->id, 1, 300, ['config_location' => $oldFile]);
    retentionConfig($this->device->id, 2, 200, ['config_location' => $keptFile, 'latest_version' => 1]);
    retentionConfig($this->device->id, 0, 300, ['config_location' => $failedFile, 'download_status' => 0]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting(30, ['non_downloaded_retention_days' => 30]));

    expect(File::exists($oldFile))->toBeFalse()
        ->and(File::exists($failedFile))->toBeFalse()
        ->and(File::exists($keptFile))->toBeTrue()
        ->and($report->totalFilesDeleted)->toBe(2);
});

test('a file still used by a remaining config row is not removed', function () {
    $sharedFile = $this->tempDir . '/shared.txt';
    File::put($sharedFile, 'shared');

    retentionConfig($this->device->id, 1, 300, ['config_location' => $sharedFile]);
    retentionConfig($this->device->id, 2, 200, ['config_location' => $sharedFile, 'latest_version' => 1]);

    $report = app(ConfigRetentionService::class)->runFromSettings(configsSetting());

    expect(Config::count())->toBe(1)
        ->and(File::exists($sharedFile))->toBeTrue()
        ->and($report->totalFilesDeleted)->toBe(0);
});

test('config summaries are recounted for the devices that lost configs', function () {
    retentionConfig($this->device->id, 1, 300);
    retentionConfig($this->device->id, 2, 200);
    retentionConfig($this->device->id, 3, 100, ['latest_version' => 1]);
    retentionConfig($this->device->id, 0, 300, ['download_status' => 0]);
    retentionConfig($this->device->id, 0, 1, ['download_status' => 0]);

    expect(ConfigSummary::where('device_id', $this->device->id)->value('total_count'))->toBe(5);

    app(ConfigRetentionService::class)->runFromSettings(configsSetting(30, ['non_downloaded_retention_days' => 30]));

    $summary = ConfigSummary::where('device_id', $this->device->id)->first();

    expect($summary->download_status_1_count)->toBe(1)
        ->and($summary->download_status_0_count)->toBe(1)
        ->and($summary->total_count)->toBe(2)
        ->and($summary->total_file_count)->toBe(2);
});
