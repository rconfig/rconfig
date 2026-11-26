<?php

use App\Models\PurgeRun;
use App\Models\SettingsPurge;
use App\Models\TrackedJob;
use App\Models\User;
use App\Notifications\DBPurgeOperationNotification;
use App\Notifications\MailPurgeOperationNotification;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->beginTransaction();
    Notification::fake();
    Queue::fake();
    PurgeRun::query()->delete();
    TrackedJob::query()->delete();
    $this->recipient = User::factory()->create(['get_notifications' => true]);
});

afterEach(function () {
    $this->rollBackTransaction();
});

function enableTrackedJobsPurge(): void
{
    SettingsPurge::where('target', 'tracked_jobs')->update([
        'retention_days' => 30,
        'options' => json_encode(['batch_size' => 100, 'cleanup_frequency' => 'daily', 'enabled' => true]),
    ]);
}

test('the migration seeds the six purge targets and every one is disabled', function () {
    $settings = SettingsPurge::orderBy('target')->get();

    expect($settings->pluck('target')->all())->toBe(['activity_log', 'configs', 'failed_jobs', 'job_batches', 'jobs', 'tracked_jobs'])
        ->and($settings->filter->isEnabled())->toBeEmpty()
        ->and($settings->firstWhere('target', 'configs')->options)->toMatchArray(['min_versions_per_device_command' => 3, 'non_downloaded_retention_days' => 30])
        ->and($settings->firstWhere('target', 'configs')->retention_days)->toBe(1825);
});

test('the purge completed notification has a default so recipients receive it', function () {
    expect(DB::table('notifications_defaults')->where('notification_type', 'config.purge_completed')->where('default_db', 1)->exists())->toBeTrue();
});

test('with the shipped defaults a run deletes nothing and notifies nobody', function () {
    TrackedJob::factory()->count(3)->create(['created_at' => now()->subDays(400)]);

    $this->artisan('rconfig:data-purge --no-queue')->assertSuccessful();

    $run = PurgeRun::sole();

    expect(TrackedJob::count())->toBe(3)
        ->and($run->status)->toBe('success')
        ->and($run->summary['metrics']['total_checked'])->toBe(6)
        ->and($run->summary['metrics']['total_deleted_directly'])->toBe(0);

    Notification::assertNothingSent();
});

test('a live run purges, records the run and notifies once per channel', function () {
    enableTrackedJobsPurge();
    TrackedJob::factory()->count(3)->create(['created_at' => now()->subDays(60)]);
    TrackedJob::factory()->create(['created_at' => now()->subDay()]);

    $this->artisan('rconfig:data-purge --no-queue')
        ->expectsOutputToContain('Deleted 3 records directly')
        ->assertSuccessful();

    $run = PurgeRun::sole();

    expect(TrackedJob::count())->toBe(1)
        ->and($run)->type->toBe('data_purge')->mode->toBe('purge')->dry_run->toBeFalse()->status->toBe('success')
        ->and($run->finished_at)->not->toBeNull()
        ->and($run->summary['options']['no_queue'])->toBeTrue()
        ->and($run->summary['metrics']['total_deleted_directly'])->toBe(3);

    Notification::assertSentToTimes($this->recipient, DBPurgeOperationNotification::class, 1);
    Notification::assertSentToTimes($this->recipient, MailPurgeOperationNotification::class, 1);
    Notification::assertSentTo($this->recipient, DBPurgeOperationNotification::class, function (DBPurgeOperationNotification $notification): bool {
        return str_contains($notification->toArray($this->recipient)['description'], 'tracked_jobs: Deleted 3 records directly');
    });
});

test('a dry run records the run, deletes nothing and sends no notification', function () {
    enableTrackedJobsPurge();
    TrackedJob::factory()->count(2)->create(['created_at' => now()->subDays(60)]);

    $this->artisan('rconfig:data-purge --dry-run')
        ->expectsOutputToContain('Would delete 2 records')
        ->assertSuccessful();

    expect(TrackedJob::count())->toBe(2)
        ->and(PurgeRun::sole()->dry_run)->toBeTrue();

    Notification::assertNothingSent();
});

test('the table option limits the run to one target', function () {
    enableTrackedJobsPurge();

    $this->artisan('rconfig:data-purge --table=tracked_jobs --no-queue')->assertSuccessful();

    $run = PurgeRun::sole();

    expect($run->scope)->toBe(['table' => 'tracked_jobs'])
        ->and($run->summary['metrics']['total_checked'])->toBe(1);
});

test('a run with errors is recorded as completed with errors and notifies', function () {
    SettingsPurge::factory()->forTarget('users')->withRetentionDays(1)->enabled()->create();

    $this->artisan('rconfig:data-purge --no-queue')->assertSuccessful();

    expect(PurgeRun::sole()->status)->toBe('completed_with_errors');

    Notification::assertSentTo($this->recipient, DBPurgeOperationNotification::class);
});

test('the purge is scheduled nightly at 02:30 and always queues large deletes', function () {
    app()->forgetInstance(Schedule::class);

    $event = collect(app(Schedule::class)->events())->first(fn (Event $event): bool => str_contains((string) $event->command, 'rconfig:data-purge'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('30 2 * * *')
        ->and($event->command)->toContain('--force-queue')
        ->and($event->withoutOverlapping)->toBeTrue();
});
