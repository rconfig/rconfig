<?php

use App\HealthChecks\WorkerPrivilegeCheck;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * Every OS call goes through the faked HostFilesystem: the process list, the
 * root and Docker checks, the cron.d file, the Supervisor config and root's
 * cron table. The cache is the array store, so the root scheduler marker
 * never reaches Redis. The full set of checks is covered in
 * WorkerUserCheckServiceTest; this file covers how the dashboard shows them.
 */
beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    $this->host = $this->fakeHealthyWorkerBox();
});

/**
 * @return array{pid: int, uid: int, arguments: list<string>}
 */
function horizonProcess(int $uid, ?string $artisan = null, string $subcommand = 'horizon'): array
{
    return [
        'pid' => 4000 + $uid,
        'uid' => $uid,
        'arguments' => ['php', $artisan ?? base_path('artisan'), $subcommand],
    ];
}

function runWorkerPrivilegeCheck(): Result
{
    return app(WorkerPrivilegeCheck::class)->run();
}

test('it is ok when Horizon runs as the web user and the cron.d file is in place', function () {
    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::ok()))->toBeTrue()
        ->and($result->getShortSummary())->toBe('Ok')
        ->and($result->meta['problems'])->toBe([]);
});

test('it warns when this install\'s Horizon runs as root and points at update.sh', function () {
    $this->host->processList = [horizonProcess(0)];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getShortSummary())->toBe('Run sudo ./update.sh')
        ->and($result->getNotificationMessage())->toContain('Horizon runs as root')
        ->and($result->getNotificationMessage())->toContain('sudo ./update.sh');
});

test('it ignores a root Horizon that belongs to another app folder', function (string $artisan) {
    $this->host->processList[] = horizonProcess(0, $artisan);

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
})->with([
    'rConfig PRO' => '/var/www/html/rconfig8/artisan',
    'a folder sharing a prefix' => fn (): string => base_path() . '8/artisan',
]);

test('it ignores Horizon subcommands running as root', function () {
    $this->host->processList[] = horizonProcess(0, subcommand: 'horizon:work');

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
});

test('it warns when the check itself runs as root, since the scheduler runs it', function () {
    $this->host = $this->fakeHealthyWorkerBox(isRoot: true);

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('The scheduler ran as root')
        ->and(Cache::has(WorkerPrivilegeCheck::ROOT_SCHEDULER_CACHE_KEY))->toBeTrue();
});

test('a root scheduler run keeps warning when the check later runs as the web user', function () {
    $this->fakeHealthyWorkerBox(isRoot: true);
    runWorkerPrivilegeCheck();

    $this->fakeHealthyWorkerBox(isRoot: false);

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('The scheduler ran as root');
});

test('the root scheduler warning clears once the marker expires', function () {
    $this->fakeHealthyWorkerBox(isRoot: true);
    runWorkerPrivilegeCheck();

    $this->travel(WorkerPrivilegeCheck::ROOT_SCHEDULER_MARKER_MINUTES + 1)->minutes();

    $this->fakeHealthyWorkerBox(isRoot: false);

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
});

test('it warns when /etc/cron.d/rconfig-core is missing', function () {
    unset($this->host->pathStats[WorkerPrivilegeCheck::CRON_FILE]);

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('/etc/cron.d/rconfig-core is missing')
        ->and($result->getShortSummary())->toBe('Run sudo ./update.sh');
});

test('it lists every problem when all checks find one', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: true);
    $this->host->userNames = [0 => 'root', 48 => 'apache'];
    $this->host->processList = [horizonProcess(0)];

    $result = runWorkerPrivilegeCheck();

    expect($result->meta['problems'])->toHaveCount(4)
        ->and($result->meta['state'])->toBe('still_root')
        ->and($result->getNotificationMessage())->toStartWith('Still running as root.');
});

test('a warning alone, such as Horizon not running, does not point at update.sh', function () {
    $this->host->processList = [];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getShortSummary())->toBe('Check the worker user')
        ->and($result->getNotificationMessage())->toContain('Horizon is not running')
        ->and($result->getNotificationMessage())->not->toContain('update.sh');
});

test('it is not applicable in Docker', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: true, isDocker: true);
    $this->host->processList = [horizonProcess(0)];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::ok()))->toBeTrue()
        ->and(Cache::has(WorkerPrivilegeCheck::ROOT_SCHEDULER_CACHE_KEY))->toBeFalse();
});
