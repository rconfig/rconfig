<?php

use App\Jobs\PurgeTableJob;
use App\Models\Config;
use App\Models\Device;
use App\Models\SettingsPurge;
use App\Models\TrackedJob;
use App\Services\DataPurge\DataPurgeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->beginTransaction();
    Queue::fake();
    SettingsPurge::query()->delete();
    TrackedJob::query()->delete();
});

afterEach(function () {
    Carbon::setTestNow();
    $this->rollBackTransaction();
});

function purgeSetting(string $target, int $days = 30, array $options = []): SettingsPurge
{
    return SettingsPurge::factory()->forTarget($target)->withRetentionDays($days)->create([
        'options' => array_merge(['batch_size' => 2, 'cleanup_frequency' => 'daily', 'enabled' => true], $options),
    ]);
}

function trackedJobsAged(int $old, int $recent): void
{
    TrackedJob::factory()->count($old)->create(['created_at' => now()->subDays(60)]);
    TrackedJob::factory()->count($recent)->create(['created_at' => now()->subDay()]);
}

function tableResultFor(array $results, string $table): array
{
    return collect($results['tables_processed'])->firstWhere('table', $table);
}

test('deletes rows older than the retention cutoff and keeps newer rows', function () {
    purgeSetting('tracked_jobs');
    trackedJobsAged(5, 3);

    $results = app(DataPurgeService::class)->execute();

    expect(TrackedJob::count())->toBe(3)
        ->and($results['total_checked'])->toBe(1)
        ->and($results['total_deleted_directly'])->toBe(5)
        ->and(tableResultFor($results, 'tracked_jobs'))->toMatchArray(['status' => 'success', 'records_found' => 5, 'records_deleted' => 5]);
});

test('a disabled target is skipped and nothing is deleted', function () {
    purgeSetting('tracked_jobs', 30, ['enabled' => false]);
    trackedJobsAged(4, 0);

    $results = app(DataPurgeService::class)->execute();

    expect(TrackedJob::count())->toBe(4)
        ->and(tableResultFor($results, 'tracked_jobs'))->toMatchArray(['status' => 'skipped', 'message' => 'Purge is disabled']);
});

test('a target with no enabled option is treated as disabled', function () {
    SettingsPurge::factory()->forTarget('tracked_jobs')->withRetentionDays(30)->create(['options' => ['batch_size' => 100]]);
    trackedJobsAged(2, 0);

    $results = app(DataPurgeService::class)->execute();

    expect(TrackedJob::count())->toBe(2)
        ->and(tableResultFor($results, 'tracked_jobs')['status'])->toBe('skipped');
});

test('zero retention days is skipped', function () {
    purgeSetting('tracked_jobs', 0);
    trackedJobsAged(2, 0);

    $results = app(DataPurgeService::class)->execute();

    expect(TrackedJob::count())->toBe(2)
        ->and(tableResultFor($results, 'tracked_jobs')['message'])->toBe('Retention days is 0');
});

test('weekly targets only run on a sunday', function () {
    purgeSetting('tracked_jobs', 30, ['cleanup_frequency' => 'weekly']);

    Carbon::setTestNow(Carbon::parse('2026-11-23 02:30:00'));
    trackedJobsAged(2, 0);
    $monday = app(DataPurgeService::class)->execute();

    Carbon::setTestNow(Carbon::parse('2026-11-29 02:30:00'));
    $sunday = app(DataPurgeService::class)->execute();

    expect(tableResultFor($monday, 'tracked_jobs')['message'])->toBe('Not scheduled to run today')
        ->and(tableResultFor($sunday, 'tracked_jobs')['records_deleted'])->toBe(2);
});

test('monthly targets only run on the first of the month', function () {
    purgeSetting('tracked_jobs', 30, ['cleanup_frequency' => 'monthly']);

    Carbon::setTestNow(Carbon::parse('2026-11-15 02:30:00'));
    trackedJobsAged(2, 0);
    $midMonth = app(DataPurgeService::class)->execute();

    Carbon::setTestNow(Carbon::parse('2026-12-01 02:30:00'));
    $first = app(DataPurgeService::class)->execute();

    expect(tableResultFor($midMonth, 'tracked_jobs')['status'])->toBe('skipped')
        ->and(tableResultFor($first, 'tracked_jobs')['records_deleted'])->toBe(2);
});

test('a dry run reports the count and deletes nothing', function () {
    purgeSetting('tracked_jobs');
    trackedJobsAged(4, 1);

    $service = app(DataPurgeService::class);
    $service->setDryRun(true);
    $results = $service->execute();

    expect(TrackedJob::count())->toBe(5)
        ->and($results['total_deleted_directly'])->toBe(0)
        ->and(tableResultFor($results, 'tracked_jobs'))->toMatchArray(['status' => 'dry_run', 'records_found' => 4, 'message' => 'Would delete 4 records']);

    Queue::assertNothingPushed();
});

test('rows at or above the queue threshold are handed to the purge job', function () {
    purgeSetting('tracked_jobs', 30, ['batch_size' => 250]);
    trackedJobsAged(3, 0);

    $service = app(DataPurgeService::class);
    $service->setQueueThreshold(3);
    $results = $service->execute();

    expect(TrackedJob::count())->toBe(3)
        ->and($results['total_queued'])->toBe(1)
        ->and(tableResultFor($results, 'tracked_jobs'))->toMatchArray(['status' => 'queued', 'queued' => true]);

    Queue::assertPushedOn(DataPurgeService::QUEUE, PurgeTableJob::class, function (PurgeTableJob $job): bool {
        return $job->getTable() === 'tracked_jobs' && $job->getTimestampCol() === 'created_at' && $job->getBatchSize() === 250;
    });
});

test('force queue always queues and no queue never does', function () {
    purgeSetting('tracked_jobs');
    trackedJobsAged(1, 0);

    $forced = app(DataPurgeService::class);
    $forced->setForceQueue(true);
    $forcedResults = $forced->execute();

    $direct = app(DataPurgeService::class);
    $direct->setQueueThreshold(1);
    $direct->setNoQueue(true);
    $directResults = $direct->execute();

    expect($forcedResults['total_queued'])->toBe(1)
        ->and($directResults['total_queued'])->toBe(0)
        ->and($directResults['total_deleted_directly'])->toBe(1);
});

test('a target outside the allow-list is refused', function () {
    purgeSetting('users', 1);
    $usersBefore = DB::table('users')->count();

    $results = app(DataPurgeService::class)->execute();

    expect(DB::table('users')->count())->toBe($usersBefore)
        ->and(tableResultFor($results, 'users'))->toMatchArray(['status' => 'error', 'message' => 'Not a supported purge target'])
        ->and($results['errors'])->toHaveCount(1);
});

test('integer unix timestamp columns are purged by age', function (string $table, array $row) {
    purgeSetting($table, 7);
    DB::table($table)->delete();
    DB::table($table)->insert(array_merge($row, ['created_at' => now()->subDays(30)->timestamp], $table === 'job_batches' ? ['id' => 'old-batch'] : []));
    DB::table($table)->insert(array_merge($row, ['created_at' => now()->subDay()->timestamp], $table === 'job_batches' ? ['id' => 'new-batch'] : []));

    $results = app(DataPurgeService::class)->execute();

    expect(DB::table($table)->count())->toBe(1)
        ->and(DB::table($table)->value('created_at'))->toBeGreaterThan(now()->subDays(7)->timestamp)
        ->and(tableResultFor($results, $table)['records_deleted'])->toBe(1);
})->with([
    'jobs' => ['jobs', ['queue' => 'rConfigDefault', 'payload' => '{}', 'attempts' => 0, 'available_at' => 0]],
    'job_batches' => ['job_batches', ['name' => 'batch', 'total_jobs' => 1, 'pending_jobs' => 0, 'failed_jobs' => 0, 'failed_job_ids' => '[]']],
]);

test('failed jobs are purged on their failed_at column', function () {
    purgeSetting('failed_jobs', 7);
    DB::table('failed_jobs')->delete();

    foreach ([30, 1] as $index => $daysAgo) {
        DB::table('failed_jobs')->insert([
            'uuid' => "purge-test-{$index}",
            'connection' => 'redis',
            'queue' => 'rConfigDefault',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now()->subDays($daysAgo),
        ]);
    }

    app(DataPurgeService::class)->execute();

    expect(DB::table('failed_jobs')->pluck('uuid')->all())->toBe(['purge-test-1']);
});

test('activity log rows are purged by age', function () {
    purgeSetting('activity_log', 90);
    DB::table('activity_log')->delete();
    DB::table('activity_log')->insert(['log_name' => 'info', 'description' => 'old', 'created_at' => now()->subDays(120), 'updated_at' => now()->subDays(120)]);
    DB::table('activity_log')->insert(['log_name' => 'info', 'description' => 'recent', 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)]);

    app(DataPurgeService::class)->execute();

    expect(DB::table('activity_log')->where('description', 'old')->exists())->toBeFalse()
        ->and(DB::table('activity_log')->where('description', 'recent')->exists())->toBeTrue();
});

test('a single table can be purged on its own', function () {
    purgeSetting('tracked_jobs');
    purgeSetting('failed_jobs');
    trackedJobsAged(2, 0);

    $results = app(DataPurgeService::class)->execute('tracked_jobs');

    expect($results['total_checked'])->toBe(1)
        ->and($results['tables_processed'])->toHaveCount(1);
});

test('the configs target runs config retention and reports its totals', function () {
    Config::query()->delete();
    $device = Device::factory()->create();
    purgeSetting('configs', 30, ['min_versions_per_device_command' => 1]);

    foreach ([1 => 100, 2 => 90, 3 => 1] as $version => $daysAgo) {
        Config::factory()->create([
            'device_id' => $device->id,
            'command' => 'show run',
            'download_status' => 1,
            'config_version' => $version,
            'latest_version' => $version === 3 ? 1 : 0,
            'config_location' => null,
            'end_time' => now()->subDays($daysAgo),
        ]);
    }

    $results = app(DataPurgeService::class)->execute();

    expect(Config::where('device_id', $device->id)->pluck('config_version')->all())->toBe([3])
        ->and($results['total_deleted_directly'])->toBe(2)
        ->and(tableResultFor($results, 'configs'))->toMatchArray(['status' => 'success', 'records_deleted' => 2]);
});
