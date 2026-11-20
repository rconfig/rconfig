<?php

use App\Jobs\RunSecurityChecksJob;
use App\Models\SecurityCheckRun;
use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailHealthCheckNotification;
use App\Services\Security\SecurityCheckRegistry;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\Security\SecurityReviewSettings;
use App\Services\Security\SecurityTaskStatus;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\Support\Security\StubSecurityCheck;

beforeEach(function () {
    $this->beginTransaction();
    Notification::fake();
    StubSecurityCheck::reset();
    SecurityCheckRun::query()->delete();
    app(SecurityTaskStatus::class)->releaseRun();
    app()->instance(SecurityCheckRegistry::class, new SecurityCheckRegistry(app(), StubSecurityCheck::classes()));
    app(SecurityReviewSettings::class)->setNotify(true);
    $this->recipient = User::factory()->create(['get_notifications' => true]);
});

afterEach(function () {
    app(SecurityTaskStatus::class)->releaseRun();
    $this->rollBackTransaction();
});

function runScheduledSecurityJob(): void
{
    app()->call([RunSecurityChecksJob::scheduled(), 'handle']);
}

function securityMailsTo(User $user): Collection
{
    return Notification::sent($user, MailHealthCheckNotification::class);
}

test('a page run marks its task done with the summary and frees the lock', function () {
    $tasks = app(SecurityTaskStatus::class);
    $token = $tasks->create(1);
    Cache::lock(SecurityTaskStatus::RUN_LOCK, 600)->get();

    app()->call([new RunSecurityChecksJob($token, null), 'handle']);

    expect($tasks->get($token)['status'])->toBe('done')
        ->and($tasks->get($token)['result']['summary']['pass'])->toBe(3)
        ->and(SecurityCheckRun::latestRun()->trigger)->toBe(SecurityContext::TRIGGER_UI)
        ->and(Cache::lock(SecurityTaskStatus::RUN_LOCK, 1)->get())->toBeTrue();
});

test('the scheduled run takes the shared lock and skips while a page run holds it', function () {
    Cache::lock(SecurityTaskStatus::RUN_LOCK, 600)->get();

    runScheduledSecurityJob();
    expect(SecurityCheckRun::query()->count())->toBe(0);

    app(SecurityTaskStatus::class)->releaseRun();
    runScheduledSecurityJob();

    expect(SecurityCheckRun::query()->count())->toBe(1)
        ->and(SecurityCheckRun::latestRun()->trigger)->toBe(SecurityContext::TRIGGER_SCHEDULE);
});

test('the daily run is scheduled on the default queue at the configured time', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event): bool => $event->description === 'security-review-daily-check');

    expect($event)->toBeInstanceOf(CallbackEvent::class)
        ->and($event->expression)->toBe('15 4 * * *');
});

test('one email when a check starts failing, none while it keeps failing, one when it recovers', function () {
    runScheduledSecurityJob();
    expect(securityMailsTo($this->recipient))->toHaveCount(0);

    StubSecurityCheck::$results['app.alpha'] = SecurityCheckResult::fail('Alpha fails.');
    StubSecurityCheck::$results['redis.gamma'] = SecurityCheckResult::fail('Gamma fails.');
    runScheduledSecurityJob();

    $mails = securityMailsTo($this->recipient);
    expect($mails)->toHaveCount(1)
        ->and($mails->first()->alert->recovered)->toBeFalse()
        ->and($mails->first()->alert->checkLabels())->toBe(['Alpha', 'Gamma'])
        ->and($mails->first()->toMail($this->recipient)->subject)->toBe('rConfig Security Check Failed')
        ->and($mails->first()->toMail($this->recipient)->actionText)->toBe('View Security Center');
    Notification::assertSentTo($this->recipient, DBNotification::class);

    runScheduledSecurityJob();
    expect(securityMailsTo($this->recipient))->toHaveCount(1);

    unset(StubSecurityCheck::$results['app.alpha']);
    runScheduledSecurityJob();

    $mails = securityMailsTo($this->recipient);
    expect($mails)->toHaveCount(2)
        ->and($mails->last()->alert->recovered)->toBeTrue()
        ->and($mails->last()->alert->checkLabels())->toBe(['Alpha']);
});

test('warnings never alert', function () {
    runScheduledSecurityJob();
    StubSecurityCheck::$results['app.beta'] = SecurityCheckResult::warn('Beta warns.');
    runScheduledSecurityJob();

    expect(securityMailsTo($this->recipient))->toHaveCount(0);
});

test('no alert when the switch is off', function () {
    app(SecurityReviewSettings::class)->setNotify(false);

    runScheduledSecurityJob();
    StubSecurityCheck::$results['app.alpha'] = SecurityCheckResult::fail('Alpha fails.');
    runScheduledSecurityJob();

    Notification::assertNothingSent();
});
