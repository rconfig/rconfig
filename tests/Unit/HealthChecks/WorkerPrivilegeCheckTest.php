<?php

use App\HealthChecks\WorkerPrivilegeCheck;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * Every OS call goes through the faked HostFilesystem: the process list, the
 * root check, the Docker check and whether /etc/cron.d/rconfig-core exists.
 * The cache is the array store, so the root scheduler marker never reaches
 * Redis.
 */
beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    $this->host = $this->fakeHostFilesystem(isRoot: false);
    $this->host->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];
    $this->host->userNames = [0 => 'root', 48 => 'apache'];
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
    $this->host->processList = [horizonProcess(48)];

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
        ->and($result->getNotificationMessage())->toContain('Horizon is running as root')
        ->and($result->getNotificationMessage())->toContain('sudo ./update.sh');
});

test('it ignores a root Horizon that belongs to another app folder', function (string $artisan) {
    $this->host->processList = [horizonProcess(0, $artisan)];

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
})->with([
    'rConfig PRO' => '/var/www/html/rconfig8/artisan',
    'a folder sharing a prefix' => fn (): string => base_path() . '8/artisan',
]);

test('it ignores Horizon subcommands running as root', function () {
    $this->host->processList = [horizonProcess(0, subcommand: 'horizon:work')];

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
});

test('it warns when the check itself runs as root, since the scheduler runs it', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: true);
    $this->host->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain("root's crontab")
        ->and(Cache::has(WorkerPrivilegeCheck::ROOT_SCHEDULER_CACHE_KEY))->toBeTrue();
});

test('a root scheduler run keeps warning when the check later runs as the web user', function () {
    $root = $this->fakeHostFilesystem(isRoot: true);
    $root->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];
    runWorkerPrivilegeCheck();

    $web = $this->fakeHostFilesystem(isRoot: false);
    $web->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('scheduler is running as root');
});

test('the root scheduler warning clears once the marker expires', function () {
    $root = $this->fakeHostFilesystem(isRoot: true);
    $root->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];
    runWorkerPrivilegeCheck();

    $this->travel(WorkerPrivilegeCheck::ROOT_SCHEDULER_MARKER_MINUTES + 1)->minutes();

    $web = $this->fakeHostFilesystem(isRoot: false);
    $web->existingPaths = [WorkerPrivilegeCheck::CRON_FILE];

    expect(runWorkerPrivilegeCheck()->status->equals(Status::ok()))->toBeTrue();
});

test('it warns when /etc/cron.d/rconfig-core is missing', function () {
    $this->host->existingPaths = [];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::warning()))->toBeTrue()
        ->and($result->getNotificationMessage())->toContain('/etc/cron.d/rconfig-core is missing')
        ->and($result->getShortSummary())->toBe('Run sudo ./update.sh');
});

test('it lists every problem when all three are true', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: true);
    $this->host->existingPaths = [];
    $this->host->processList = [horizonProcess(0)];

    expect(runWorkerPrivilegeCheck()->meta['problems'])->toHaveCount(3);
});

test('it is not applicable in Docker', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: true, isDocker: true);
    $this->host->existingPaths = [];
    $this->host->processList = [horizonProcess(0)];

    $result = runWorkerPrivilegeCheck();

    expect($result->status->equals(Status::ok()))->toBeTrue()
        ->and(Cache::has(WorkerPrivilegeCheck::ROOT_SCHEDULER_CACHE_KEY))->toBeFalse();
});
