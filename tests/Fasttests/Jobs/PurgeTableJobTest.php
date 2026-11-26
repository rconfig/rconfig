<?php

use App\Jobs\PurgeTableJob;
use App\Models\TrackedJob;
use App\Services\DataPurge\DataPurgeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->beginTransaction();
    TrackedJob::query()->delete();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function purgeCutoff(): string
{
    return now()->subDays(30)->toDateTimeString();
}

test('it deletes every row older than the cutoff in batches and keeps the rest', function () {
    Queue::fake();
    TrackedJob::factory()->count(7)->create(['created_at' => now()->subDays(60)]);
    TrackedJob::factory()->count(2)->create(['created_at' => now()->subDay()]);

    (new PurgeTableJob('tracked_jobs', purgeCutoff(), 'created_at', 3))->handle();

    expect(TrackedJob::count())->toBe(2);
    Queue::assertNothingPushed();
});

test('it queues a follow-up job when it runs out of budget with rows remaining', function () {
    Queue::fake();
    TrackedJob::factory()->count(5)->create(['created_at' => now()->subDays(60)]);

    (new PurgeTableJob('tracked_jobs', purgeCutoff(), 'created_at', 2, 30, 1))->handle();

    expect(TrackedJob::count())->toBe(3);

    Queue::assertPushedOn(DataPurgeService::QUEUE, PurgeTableJob::class, function (PurgeTableJob $job): bool {
        return $job->getTable() === 'tracked_jobs' && $job->getBatchSize() === 2 && $job->getCutoff() === purgeCutoff();
    });
});

test('the follow-up jobs finish the purge', function () {
    TrackedJob::factory()->count(5)->create(['created_at' => now()->subDays(60)]);

    PurgeTableJob::dispatch('tracked_jobs', purgeCutoff(), 'created_at', 2, 30, 1);

    expect(TrackedJob::count())->toBe(0);
});

test('it refuses a table outside the allow-list', function () {
    $usersBefore = DB::table('users')->count();

    (new PurgeTableJob('users', now()->addDay()->toDateTimeString(), 'created_at'))->handle();

    expect(DB::table('users')->count())->toBe($usersBefore);
});

test('it does nothing when the timestamp column is missing', function () {
    TrackedJob::factory()->count(2)->create(['created_at' => now()->subDays(60)]);

    (new PurgeTableJob('tracked_jobs', purgeCutoff(), 'no_such_column'))->handle();

    expect(TrackedJob::count())->toBe(2);
});

test('it is configured to finish inside the horizon worker timeout', function () {
    $job = new PurgeTableJob('tracked_jobs', purgeCutoff(), 'created_at');

    expect($job->queue)->toBe('PurgeDataJobs')
        ->and($job->timeout)->toBeLessThan((int) config('horizon.environments.production.HorizonOne.timeout'))
        ->and($job->tags())->toBe(['purge', 'table:tracked_jobs']);
});
