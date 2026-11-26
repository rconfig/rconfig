<?php

use App\Jobs\PurgeTableJob;
use App\Models\SettingsPurge;
use App\Services\DataPurge\DataPurgeService;
use App\Services\DataPurge\TableStats;

test('the purge queue is worked by a horizon supervisor in every environment', function (string $environment) {
    $workedQueues = collect(config("horizon.environments.{$environment}"))->pluck('queue')->flatten()->all();

    expect($workedQueues)->toContain(DataPurgeService::QUEUE)
        ->and((new PurgeTableJob('tracked_jobs', '2026-01-01 00:00:00', 'created_at'))->queue)->toBe(DataPurgeService::QUEUE);
})->with(['production', 'local']);

test('the configs target is never handled by the generic table purge', function () {
    expect(SettingsPurge::GENERIC_TARGETS)->not->toContain(SettingsPurge::TARGET_CONFIGS)
        ->and(SettingsPurge::GENERIC_TARGETS)->toBe(['activity_log', 'failed_jobs', 'job_batches', 'jobs', 'tracked_jobs']);
});

test('byte sizes are shown in the right unit', function (int $bytes, string $expected) {
    expect(TableStats::humanBytes($bytes))->toBe($expected);
})->with([
    [0, '0 B'],
    [1023, '1023 B'],
    [1024, '1.00 KB'],
    [2048, '2.00 KB'],
    [1048576, '1.00 MB'],
    [1610612736, '1.50 GB'],
]);
