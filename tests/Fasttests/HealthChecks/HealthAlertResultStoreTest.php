<?php

use App\HealthChecks\Notifications\HealthAlertResultStore;
use App\Services\Notifications\HealthCheckNotifier;
use App\Services\Notifications\Shared\HealthCheckAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Enums\Status;
use Spatie\Health\Facades\Health;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\ResultStore;

beforeEach(function () {
    $this->beginTransaction();

    HealthCheckResultHistoryItem::query()->delete();

    $this->alerts = new ArrayObject;
    $alerts = $this->alerts;
    $notifier = Mockery::mock(HealthCheckNotifier::class);
    $notifier->shouldReceive('notify')->andReturnUsing(function (HealthCheckAlert $alert) use ($alerts): bool {
        $alerts->append($alert);

        return true;
    });
    $this->app->instance(HealthCheckNotifier::class, $notifier);
});

afterEach(function () {
    Health::clearChecks();
    $this->rollBackTransaction();
});

function healthFakeCheck(string $name, string $status): Check
{
    $check = new class extends Check
    {
        public string $fakeStatus = 'ok';

        public function run(): Result
        {
            $result = Result::make()->shortSummary($this->fakeStatus);

            return match ($this->fakeStatus) {
                'failed' => $result->failed($this->getName() . ' is failed'),
                'warning' => $result->warning($this->getName() . ' is warning'),
                'crashed' => new Result(Status::crashed(), $this->getName() . ' crashed'),
                'skipped' => new Result(Status::skipped()),
                default => $result->ok(),
            };
        }
    };

    $check->fakeStatus = $status;

    return $check->name($name);
}

/**
 * @param  array<string, string>  $statuses
 * @return Collection<int, Result>
 */
function healthResults(array $statuses): Collection
{
    return collect($statuses)
        ->map(function (string $status, string $name): Result {
            $check = healthFakeCheck($name, $status);

            return $check->run()->check($check);
        })
        ->values();
}

function healthStorePrevious(string $name, string $status): void
{
    HealthCheckResultHistoryItem::query()->create([
        'check_name' => $name,
        'check_label' => $name,
        'status' => $status,
        'notification_message' => '',
        'short_summary' => '',
        'meta' => [],
        'batch' => (string) Str::uuid(),
        'ended_at' => now(),
    ]);
}

function healthAlertStore(): HealthAlertResultStore
{
    return app(HealthAlertResultStore::class);
}

test('a check that starts failing alerts once', function () {
    healthStorePrevious('Redis', 'ok');

    healthAlertStore()->save(healthResults(['Redis' => 'failed']));

    expect($this->alerts)->toHaveCount(1)
        ->and($this->alerts[0]->recovered)->toBeFalse()
        ->and($this->alerts[0]->checkLabels())->toBe(['Redis'])
        ->and($this->alerts[0]->checks[0]['message'])->toBe('Redis is failed');
});

test('a check that stays failed does not alert again', function () {
    healthStorePrevious('Redis', 'failed');

    healthAlertStore()->save(healthResults(['Redis' => 'failed']));

    expect($this->alerts)->toHaveCount(0);
});

test('a failed check that passes again sends a recovered alert', function () {
    healthStorePrevious('Redis', 'failed');

    healthAlertStore()->save(healthResults(['Redis' => 'ok']));

    expect($this->alerts)->toHaveCount(1)
        ->and($this->alerts[0]->recovered)->toBeTrue();
});

test('a failed check that drops to warning counts as recovered', function () {
    healthStorePrevious('Disk', 'failed');

    healthAlertStore()->save(healthResults(['Disk' => 'warning']));

    expect($this->alerts)->toHaveCount(1)
        ->and($this->alerts[0]->recovered)->toBeTrue();
});

test('warnings never alert', function () {
    healthStorePrevious('Disk', 'ok');

    healthAlertStore()->save(healthResults(['Disk' => 'warning']));

    expect($this->alerts)->toHaveCount(0);
});

test('a check with no history that fails alerts', function () {
    healthAlertStore()->save(healthResults(['Redis' => 'failed', 'Database' => 'ok']));

    expect($this->alerts)->toHaveCount(1)
        ->and($this->alerts[0]->checkLabels())->toBe(['Redis']);
});

test('a crashed check counts as failing', function () {
    healthStorePrevious('Redis', 'ok');

    healthAlertStore()->save(healthResults(['Redis' => 'crashed']));

    expect($this->alerts)->toHaveCount(1)
        ->and($this->alerts[0]->recovered)->toBeFalse();
});

test('checks changing in one run send one failed and one recovered alert', function () {
    healthStorePrevious('Redis', 'ok');
    healthStorePrevious('Horizon', 'ok');
    healthStorePrevious('Cache', 'failed');

    healthAlertStore()->save(healthResults(['Redis' => 'failed', 'Horizon' => 'failed', 'Cache' => 'ok']));

    expect($this->alerts)->toHaveCount(2)
        ->and($this->alerts[0]->checkLabels())->toBe(['Redis', 'Horizon'])
        ->and($this->alerts[1]->recovered)->toBeTrue()
        ->and($this->alerts[1]->checkLabels())->toBe(['Cache']);
});

test('skipped results are ignored and do not mask the last real status', function () {
    healthStorePrevious('Ping', 'failed');
    healthStorePrevious('Ping', 'skipped');

    healthAlertStore()->save(healthResults(['Ping' => 'skipped']));
    healthAlertStore()->save(healthResults(['Ping' => 'failed']));

    expect($this->alerts)->toHaveCount(0);
});

test('latest results are read from the eloquent store', function () {
    healthStorePrevious('Redis', 'failed');

    expect(app(ResultStore::class))->toBeInstanceOf(HealthAlertResultStore::class)
        ->and(app(ResultStore::class)->latestResults()->storedCheckResults->pluck('name')->all())->toBe(['Redis']);
});

test('a notifier error does not stop the run being stored', function () {
    $notifier = Mockery::mock(HealthCheckNotifier::class);
    $notifier->shouldReceive('notify')->andThrow(new RuntimeException('notifier on fire'));
    $this->app->instance(HealthCheckNotifier::class, $notifier);

    Health::clearChecks()->checks([healthFakeCheck('Redis', 'failed')]);

    $this->artisan(RunHealthChecksCommand::class)->assertSuccessful();

    expect((string) HealthCheckResultHistoryItem::query()->where('check_name', 'Redis')->value('status'))->toBe('failed');
});

test('health:check alerts on the transition only', function () {
    Health::clearChecks()->checks([healthFakeCheck('Redis', 'failed')]);

    $this->artisan(RunHealthChecksCommand::class)->assertSuccessful();
    $this->artisan(RunHealthChecksCommand::class)->assertSuccessful();

    expect($this->alerts)->toHaveCount(1)
        ->and(HealthCheckResultHistoryItem::query()->where('check_name', 'Redis')->count())->toBe(2);
});
