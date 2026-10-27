<?php

use App\HealthChecks\WorkerPrivilegeCheck;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Enums\Status;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * Every OS call goes through the faked HostFilesystem, so nothing reads the
 * real /proc, /etc or root's cron table. The cache is the array store, so the
 * root scheduler marker never reaches Redis. The individual checks are
 * covered in WorkerUserCheckServiceTest; this file covers the output, the
 * exit code and the JSON.
 */
beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();

    $this->host = $this->fakeHealthyWorkerBox();
});

/**
 * @return array{0: int, 1: string}
 */
function runCheckWorkerUser(array $parameters = []): array
{
    $exitCode = Artisan::call('rconfig:check-worker-user', $parameters);

    return [$exitCode, Artisan::output()];
}

/**
 * Turns the fake healthy box into a root box from before RCO-1785: Horizon
 * as root, and the old symlinked Supervisor file in place of the new ones.
 */
function makeStillOnRoot(HostFilesystem $host): void
{
    $app = base_path();

    $host->processList = [['pid' => 1234, 'uid' => 0, 'arguments' => ['php', "{$app}/artisan", 'horizon']]];
    $host->pathStats = ['/etc/supervisord.d/horizon_supervisor.ini' => ['uid' => 0, 'gid' => 0, 'mode' => 0777, 'type' => 'link']];
    $host->resolvedPaths = ['/etc/supervisord.d/horizon_supervisor.ini' => "{$app}/horizon_supervisor.ini"];
    $host->crontabs['root'] = ['exitCode' => 0, 'output' => "* * * * * cd {$app} && php artisan schedule:run >> /dev/null 2>&1\n", 'errorOutput' => ''];
}

test('an all good box prints one OK line per check, the state, and exits 0', function () {
    [$exitCode, $output] = runCheckWorkerUser();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Web user: apache')
        ->and($output)->toContain('OK   Horizon: Horizon runs as apache (PID 4321).')
        ->and($output)->toContain('OK   Scheduler cron file: /etc/cron.d/rconfig-core is root:root 0644 and runs the scheduler as apache.')
        ->and($output)->toContain("SKIP Root crontab: Not running as root, so root's cron table was not read.")
        ->and($output)->toContain('OK   Supervisor config: /etc/supervisord.d/rconfig-core-horizon.ini is root:root 0644 and runs Horizon as apache.')
        ->and($output)->toContain('Overall: All good')
        ->and($output)->not->toContain('Fix:');
});

test('a box still on root fails each check, says so and gives the fix', function () {
    $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
    makeStillOnRoot($this->host);

    [$exitCode, $output] = runCheckWorkerUser();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Web user: apache (checked as root)')
        ->and($output)->toContain('FAIL Horizon: Horizon runs as root (PID 1234). It must run as apache only.')
        ->and($output)->toContain('FAIL Scheduler cron file: /etc/cron.d/rconfig-core is missing')
        ->and($output)->toContain("FAIL Root crontab: Root's cron table still runs this install's scheduler as root")
        ->and($output)->toContain('FAIL Supervisor config: /etc/supervisord.d/rconfig-core-horizon.ini is missing')
        ->and($output)->toContain('Overall: Still running as root')
        ->and($output)->toContain('Fix: Run sudo ./update.sh from ' . base_path());
});

test('root\'s cron table is only read as root, and reported as skipped otherwise', function () {
    makeStillOnRoot($this->host);

    [, $output] = runCheckWorkerUser();

    expect($this->host->crontabReads)->toBe([])
        ->and($output)->toContain('SKIP Root crontab: Not running as root');
});

test('warnings alone exit 0', function () {
    $this->host->processList = [];

    [$exitCode, $output] = runCheckWorkerUser();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('WARN Horizon: Horizon is not running')
        ->and($output)->toContain('hidepid')
        ->and($output)->toContain('Overall: All good')
        ->and($output)->not->toContain('Fix:');
});

test('rConfig PRO\'s root Horizon on the same box does not fail the check', function () {
    $this->host->processList[] = ['pid' => 77, 'uid' => 0, 'arguments' => ['php', '/var/www/html/rconfig8/artisan', 'horizon']];

    [$exitCode, $output] = runCheckWorkerUser();

    expect($exitCode)->toBe(0)->and($output)->not->toContain('PID 77');
});

test('in Docker the bare-metal checks are skipped, it says so and exits 0', function () {
    $this->host = $this->fakeHealthyWorkerBox(isRoot: true, isDocker: true);
    makeStillOnRoot($this->host);

    [$exitCode, $output] = runCheckWorkerUser();

    expect($exitCode)->toBe(0)
        ->and(trim($output))->toBe('Docker: the container runs Horizon and the scheduler itself, so the bare-metal checks were skipped.')
        ->and($this->host->crontabReads)->toBe([]);
});

test('--json prints the whole report and nothing else', function () {
    [$exitCode, $output] = runCheckWorkerUser(['--json' => true]);
    $json = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and(array_keys($json))->toBe(['state', 'summary', 'status', 'web_user', 'checked_as_root', 'checks', 'fix'])
        ->and($json['state'])->toBe('all_good')
        ->and($json['summary'])->toBe('All good')
        ->and($json['status'])->toBe('ok')
        ->and($json['web_user'])->toBe('apache')
        ->and($json['checked_as_root'])->toBeFalse()
        ->and($json['fix'])->toBeNull()
        ->and(array_column($json['checks'], 'key'))->toBe(['horizon', 'scheduler_cron_file', 'root_crontab', 'supervisor_config'])
        ->and(array_column($json['checks'], 'status'))->toBe(['ok', 'ok', 'skipped', 'ok'])
        ->and(array_keys($json['checks'][0]))->toBe(['key', 'label', 'status', 'message', 'details'])
        ->and($json['checks'][0]['details'])->toBe(['processes' => [['pid' => 4321, 'user' => 'apache']]]);
});

test('--json keeps the exit code on failure', function () {
    $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
    makeStillOnRoot($this->host);

    [$exitCode, $output] = runCheckWorkerUser(['--json' => true]);
    $json = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(1)
        ->and($json['state'])->toBe('still_root')
        ->and($json['status'])->toBe('fail')
        ->and($json['fix'])->toBe('Run sudo ./update.sh from ' . base_path());
});

test('--json in Docker reports the skip with no checks', function () {
    $this->fakeHealthyWorkerBox(isDocker: true);

    [$exitCode, $output] = runCheckWorkerUser(['--json' => true]);
    $json = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($json['state'])->toBe('docker')
        ->and($json['status'])->toBe('skipped')
        ->and($json['checks'])->toBe([]);
});

test('the command and the dashboard check agree on the same box', function (Closure $breakBox) {
    $breakBox($this->host);

    [$exitCode, $output] = runCheckWorkerUser(['--json' => true]);
    $json = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
    $health = app(WorkerPrivilegeCheck::class)->run();

    $commandProblems = array_column(array_filter($json['checks'], fn (array $check): bool => in_array($check['status'], ['warn', 'fail'], true)), 'message');

    expect($health->meta['problems'])->toBe(array_values($commandProblems))
        ->and($health->meta['state'])->toBe($json['state'])
        ->and($health->status->equals(Status::ok()))->toBe($json['status'] === 'ok')
        ->and($exitCode === 0)->toBe($json['status'] !== 'fail');
})->with([
    'all good' => fn (): Closure => function (HostFilesystem $host): void {},
    'Horizon not running' => fn (): Closure => function (HostFilesystem $host): void {
        $host->processList = [];
    },
    'Horizon as root' => fn (): Closure => function (HostFilesystem $host): void {
        $host->processList[0]['uid'] = 0;
    },
    'cron file missing' => fn (): Closure => function (HostFilesystem $host): void {
        unset($host->pathStats['/etc/cron.d/rconfig-core']);
    },
    'cron file group writable' => fn (): Closure => function (HostFilesystem $host): void {
        $host->pathStats['/etc/cron.d/rconfig-core']['mode'] = 0664;
    },
    'Supervisor config with user=root' => fn (): Closure => function (HostFilesystem $host): void {
        $host->putFile('/etc/supervisord.d/rconfig-core-horizon.ini', "[program:rconfig-core-horizon]\nuser=root\n");
    },
    'recent root scheduler run' => fn (): Closure => function (HostFilesystem $host): void {
        Cache::put(WorkerPrivilegeCheck::ROOT_SCHEDULER_CACHE_KEY, now()->getTimestamp(), now()->addMinutes(15));
    },
    'still on root' => fn (): Closure => function (HostFilesystem $host): void {
        makeStillOnRoot($host);
    },
]);
