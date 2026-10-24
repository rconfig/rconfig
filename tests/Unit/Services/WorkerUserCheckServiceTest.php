<?php

use App\Enums\WorkerCheckStatus;
use App\Services\System\WorkerUserCheckService;
use Illuminate\Support\Facades\Cache;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

beforeEach(function () {
    $this->host = $this->fakeHostFilesystem();
    $this->host->userNames = [0 => 'root', 48 => 'apache'];
});

test('it finds this install\'s Horizon master with its user', function () {
    $this->host->processList = [
        ['pid' => 10, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon']],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses())->toBe([[
        'pid' => 10,
        'uid' => 48,
        'user' => 'apache',
        'command' => 'php ' . base_path('artisan') . ' horizon',
    ]]);
});

test('it skips other app folders, Horizon subcommands and unrelated processes', function () {
    $this->host->processList = [
        ['pid' => 11, 'uid' => 0, 'arguments' => ['php', '/var/www/html/rconfig8/artisan', 'horizon']],
        ['pid' => 12, 'uid' => 0, 'arguments' => ['php', base_path() . '8/artisan', 'horizon']],
        ['pid' => 13, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon:work', 'redis']],
        ['pid' => 14, 'uid' => 48, 'arguments' => ['php', base_path('artisan'), 'horizon:supervisor']],
        ['pid' => 15, 'uid' => 0, 'arguments' => ['/usr/bin/redis-server', '127.0.0.1:6379']],
        ['pid' => 16, 'uid' => 0, 'arguments' => []],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses())->toBe([]);
});

test('it falls back to the uid when the user has no name', function () {
    $this->host->processList = [
        ['pid' => 17, 'uid' => 4242, 'arguments' => ['php', base_path('artisan'), 'horizon']],
    ];

    expect(app(WorkerUserCheckService::class)->runningHorizonProcesses()[0]['user'])->toBe('4242');
});

test('the expected user is the web user', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Ubuntu\"\nID=ubuntu\n");

    expect(app(WorkerUserCheckService::class)->expectedUser())->toBe(['www-data', 'www-data']);
});

// --- report() ---------------------------------------------------------------------------

/**
 * One check from a report, by key.
 *
 * @param  array{checks: list<array{key: string}>}  $report
 * @return array{key: string, label: string, status: WorkerCheckStatus, message: string, details: array<string, mixed>}
 */
function reportCheck(array $report, string $key): array
{
    return collect($report['checks'])->firstWhere('key', $key);
}

/**
 * @return array<string, mixed>
 */
function workerReport(): array
{
    return app(WorkerUserCheckService::class)->report();
}

describe('report', function () {
    beforeEach(function () {
        config(['cache.default' => 'array']);
        Cache::flush();

        $this->host = $this->fakeHealthyWorkerBox();
        $this->appDir = base_path();
    });

    test('a correctly set up box is all good, with root\'s cron table skipped when not root', function () {
        $report = workerReport();

        expect($report['state'])->toBe('all_good')
            ->and($report['summary'])->toBe('All good')
            ->and($report['status'])->toBe(WorkerCheckStatus::Ok)
            ->and($report['fix'])->toBeNull()
            ->and($report['web_user'])->toBe('apache')
            ->and($report['checked_as_root'])->toBeFalse()
            ->and(array_column($report['checks'], 'key'))->toBe(['horizon', 'scheduler_cron_file', 'root_crontab', 'supervisor_config'])
            ->and(reportCheck($report, 'horizon')['message'])->toBe('Horizon runs as apache (PID 4321).')
            ->and(reportCheck($report, 'scheduler_cron_file')['status'])->toBe(WorkerCheckStatus::Ok)
            ->and(reportCheck($report, 'root_crontab')['status'])->toBe(WorkerCheckStatus::Skipped)
            ->and(reportCheck($report, 'root_crontab')['message'])->toContain('Not running as root')
            ->and(reportCheck($report, 'supervisor_config')['status'])->toBe(WorkerCheckStatus::Ok)
            ->and($this->host->crontabReads)->toBe([]);
    });

    // --- Horizon ---

    test('Horizon not running is a warning that mentions hidepid, not a failure', function () {
        $this->host->processList = [];

        $report = workerReport();

        expect(reportCheck($report, 'horizon')['status'])->toBe(WorkerCheckStatus::Warn)
            ->and(reportCheck($report, 'horizon')['message'])->toContain('Horizon is not running')
            ->and(reportCheck($report, 'horizon')['message'])->toContain('hidepid')
            ->and($report['status'])->toBe(WorkerCheckStatus::Warn)
            ->and($report['state'])->toBe('all_good')
            ->and($report['fix'])->toBeNull();
    });

    test('Horizon as root fails and the box is still running as root when nothing else is in place', function () {
        $this->host->processList = [['pid' => 99, 'uid' => 0, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']]];
        $this->host->pathStats = [];

        $report = workerReport();

        expect(reportCheck($report, 'horizon')['status'])->toBe(WorkerCheckStatus::Fail)
            ->and(reportCheck($report, 'horizon')['message'])->toBe('Horizon runs as root (PID 99). It must run as apache only.')
            ->and($report['state'])->toBe('still_root')
            ->and($report['summary'])->toBe('Still running as root')
            ->and($report['fix'])->toBe("Run sudo ./update.sh from {$this->appDir}");
    });

    test('Horizon as root alongside a correct cron file and Supervisor config is mixed', function () {
        $this->host->processList = [['pid' => 99, 'uid' => 0, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']]];

        expect(workerReport()['state'])->toBe('mixed');
    });

    test('two Horizon masters, one as root and one as apache, fail as mixed', function () {
        $this->host->processList[] = ['pid' => 99, 'uid' => 0, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']];

        $report = workerReport();

        expect(reportCheck($report, 'horizon')['message'])->toBe('Horizon runs as apache (PID 4321) and root (PID 99). It must run as apache only.')
            ->and(reportCheck($report, 'horizon')['details']['processes'])->toBe([['pid' => 4321, 'user' => 'apache'], ['pid' => 99, 'user' => 'root']])
            ->and($report['state'])->toBe('mixed');
    });

    test('Horizon as another user that is not root fails as misconfigured', function () {
        $this->host->userNames[1000] = 'deploy';
        $this->host->processList = [['pid' => 7, 'uid' => 1000, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']]];

        $report = workerReport();

        expect(reportCheck($report, 'horizon')['status'])->toBe(WorkerCheckStatus::Fail)
            ->and($report['state'])->toBe('misconfigured');
    });

    test('rConfig PRO\'s root Horizon on the same box is ignored', function () {
        $this->host->processList[] = ['pid' => 55, 'uid' => 0, 'arguments' => ['php', '/var/www/html/rconfig8/artisan', 'horizon']];
        $this->host->processList[] = ['pid' => 56, 'uid' => 0, 'arguments' => ['php', "{$this->appDir}8/artisan", 'horizon']];

        $report = workerReport();

        expect(reportCheck($report, 'horizon')['status'])->toBe(WorkerCheckStatus::Ok)
            ->and($report['state'])->toBe('all_good');
    });

    // --- Scheduler cron file ---

    test('a missing cron file fails', function () {
        unset($this->host->pathStats['/etc/cron.d/rconfig-core']);

        $check = reportCheck(workerReport(), 'scheduler_cron_file');

        expect($check['status'])->toBe(WorkerCheckStatus::Fail)
            ->and($check['message'])->toBe('/etc/cron.d/rconfig-core is missing, so the scheduler is not set up to run as apache.');
    });

    test('a cron file that is a symlink fails', function () {
        $this->host->pathStats['/etc/cron.d/rconfig-core']['type'] = 'link';

        expect(reportCheck(workerReport(), 'scheduler_cron_file')['message'])->toContain('is a symlink');
    });

    test('a cron file not owned by root:root fails and names the owner', function () {
        $this->host->pathStats['/etc/cron.d/rconfig-core']['uid'] = 48;

        $report = workerReport();

        expect(reportCheck($report, 'scheduler_cron_file')['status'])->toBe(WorkerCheckStatus::Fail)
            ->and(reportCheck($report, 'scheduler_cron_file')['message'])->toContain('owned by apache (uid 48, gid 0), not root:root')
            ->and($report['state'])->toBe('misconfigured');
    });

    test('a group writable cron file fails on its mode', function () {
        $this->host->pathStats['/etc/cron.d/rconfig-core']['mode'] = 0664;

        expect(reportCheck(workerReport(), 'scheduler_cron_file')['message'])->toContain('has mode 0664, not 0644');
    });

    test('a cron file that runs the scheduler as root fails as still on root', function () {
        $this->host->putFile('/etc/cron.d/rconfig-core', "* * * * * root cd {$this->appDir} && php artisan schedule:run\n");
        $this->host->processList = [];
        unset($this->host->pathStats['/etc/supervisord.d/rconfig-core-horizon.ini']);

        $report = workerReport();

        expect(reportCheck($report, 'scheduler_cron_file')['message'])->toBe('/etc/cron.d/rconfig-core runs the scheduler as root, not apache.')
            ->and($report['state'])->toBe('still_root');
    });

    test('a cron file with no line for this install fails, and PRO\'s line does not count', function () {
        $this->host->putFile('/etc/cron.d/rconfig-core', implode("\n", [
            "# * * * * * apache cd {$this->appDir} && php artisan schedule:run",
            '* * * * * apache cd /var/www/html/rconfig8 && php artisan schedule:run',
            "* * * * * apache cd {$this->appDir}8 && php artisan schedule:run",
        ]));

        expect(reportCheck(workerReport(), 'scheduler_cron_file')['message'])->toContain("has no line that runs this install's scheduler");
    });

    test('an @reboot style cron line is read with its user in the second field', function () {
        $this->host->putFile('/etc/cron.d/rconfig-core', "@hourly apache php {$this->appDir}/artisan schedule:run\n");

        expect(reportCheck(workerReport(), 'scheduler_cron_file')['status'])->toBe(WorkerCheckStatus::Ok);
    });

    test('a cron file that cannot be read fails', function () {
        unset($this->host->fileContents['/etc/cron.d/rconfig-core']);

        expect(reportCheck(workerReport(), 'scheduler_cron_file')['message'])->toContain('could not be read');
    });

    // --- Root's cron table ---

    test('as root, a clean root cron table is OK and is really read', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
        $this->host->crontabs['root'] = ['exitCode' => 0, 'output' => "0 3 * * * /usr/local/bin/backup\n", 'errorOutput' => ''];

        $report = workerReport();

        expect(reportCheck($report, 'root_crontab')['status'])->toBe(WorkerCheckStatus::Ok)
            ->and($report['checked_as_root'])->toBeTrue()
            ->and($report['state'])->toBe('all_good')
            ->and($this->host->crontabReads)->toBe(['root']);
    });

    test('as root, root with no cron table at all is OK', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true);

        expect(reportCheck(workerReport(), 'root_crontab')['status'])->toBe(WorkerCheckStatus::Ok);
    });

    test('as root, this install\'s schedule:run in root\'s cron table fails as mixed', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
        $this->host->crontabs['root'] = ['exitCode' => 0, 'output' => "* * * * * cd {$this->appDir} && php artisan schedule:run >> /dev/null 2>&1\n", 'errorOutput' => ''];

        $report = workerReport();

        expect(reportCheck($report, 'root_crontab')['status'])->toBe(WorkerCheckStatus::Fail)
            ->and(reportCheck($report, 'root_crontab')['message'])->toContain("Root's cron table still runs this install's scheduler as root")
            ->and($report['state'])->toBe('mixed');
    });

    test('as root, rConfig PRO\'s schedule:run in root\'s cron table is ignored', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
        $this->host->crontabs['root'] = ['exitCode' => 0, 'output' => "* * * * * cd /var/www/html/rconfig8 && php artisan schedule:run\n", 'errorOutput' => ''];

        expect(reportCheck(workerReport(), 'root_crontab')['status'])->toBe(WorkerCheckStatus::Ok);
    });

    test('as root, a cron table that cannot be read is a warning', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true);
        $this->host->crontabs['root'] = ['exitCode' => 1, 'output' => '', 'errorOutput' => "crontab: permission denied\n"];

        $check = reportCheck(workerReport(), 'root_crontab');

        expect($check['status'])->toBe(WorkerCheckStatus::Warn)
            ->and($check['message'])->toContain('permission denied');
    });

    test('a recent root scheduler run is a warning, both as root and not', function (bool $isRoot) {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: $isRoot);
        Cache::put(WorkerUserCheckService::ROOT_SCHEDULER_CACHE_KEY, now()->getTimestamp(), now()->addMinutes(15));

        $report = workerReport();

        expect(reportCheck($report, 'root_crontab')['status'])->toBe(WorkerCheckStatus::Warn)
            ->and(reportCheck($report, 'root_crontab')['message'])->toContain('The scheduler ran as root in the last 15 minutes')
            ->and($report['status'])->toBe(WorkerCheckStatus::Warn)
            ->and($report['fix'])->toBeNull();
    })->with(['as root' => true, 'as the web user' => false]);

    // --- Supervisor config ---

    test('the Supervisor config is looked for in the Ubuntu folder on Ubuntu', function () {
        $host = $this->fakeHostFilesystem(osRelease: "NAME=\"Ubuntu\"\nID=ubuntu\nID_LIKE=debian\n", isRoot: false);
        $host->userNames = [0 => 'root', 33 => 'www-data'];
        $host->processList = [['pid' => 1, 'uid' => 33, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']]];
        $host->putFile('/etc/cron.d/rconfig-core', "* * * * * www-data cd {$this->appDir} && php artisan schedule:run\n");
        $host->putFile('/etc/supervisor/conf.d/rconfig-core-horizon.conf', "[program:rconfig-core-horizon]\nuser=www-data\n");

        $report = workerReport();

        expect($report['state'])->toBe('all_good')
            ->and(reportCheck($report, 'supervisor_config')['details']['path'])->toBe('/etc/supervisor/conf.d/rconfig-core-horizon.conf');
    });

    test('an unknown OS is a warning for the Supervisor config', function () {
        $this->fakeHostFilesystem(osRelease: "ID=plan9\n", isRoot: false);

        expect(reportCheck(workerReport(), 'supervisor_config')['status'])->toBe(WorkerCheckStatus::Warn);
    });

    test('a missing Supervisor config fails', function () {
        unset($this->host->pathStats['/etc/supervisord.d/rconfig-core-horizon.ini']);

        expect(reportCheck(workerReport(), 'supervisor_config')['message'])->toBe('/etc/supervisord.d/rconfig-core-horizon.ini is missing, so Supervisor does not run Horizon for this install.');
    });

    test('a symlinked Supervisor config fails', function () {
        $this->host->pathStats['/etc/supervisord.d/rconfig-core-horizon.ini']['type'] = 'link';

        expect(reportCheck(workerReport(), 'supervisor_config')['message'])->toContain('is a symlink');
    });

    test('a Supervisor config not owned by root or not 0644 fails', function () {
        $this->host->pathStats['/etc/supervisord.d/rconfig-core-horizon.ini']['gid'] = 48;
        $this->host->pathStats['/etc/supervisord.d/rconfig-core-horizon.ini']['mode'] = 0666;

        $message = reportCheck(workerReport(), 'supervisor_config')['message'];

        expect($message)->toContain('not root:root')->and($message)->toContain('has mode 0666, not 0644');
    });

    test('a Supervisor config with user=root fails', function () {
        $this->host->putFile('/etc/supervisord.d/rconfig-core-horizon.ini', "[program:rconfig-core-horizon]\nuser=root\n");

        expect(reportCheck(workerReport(), 'supervisor_config')['message'])->toContain('sets user=root, not apache');
    });

    test('a Supervisor config with no user= runs Horizon as root and fails', function () {
        $this->host->putFile('/etc/supervisord.d/rconfig-core-horizon.ini', "[program:rconfig-core-horizon]\ncommand=php {$this->appDir}/artisan horizon\n");

        expect(reportCheck(workerReport(), 'supervisor_config')['message'])->toContain('has no user= line, so Supervisor runs Horizon as root');
    });

    test('an old Supervisor file that runs this install fails', function (string $kind) {
        $legacy = '/etc/supervisord.d/horizon_supervisor.ini';

        if ($kind === 'symlink') {
            $this->host->pathStats[$legacy] = ['uid' => 0, 'gid' => 0, 'mode' => 0777, 'type' => 'link'];
            $this->host->resolvedPaths[$legacy] = "{$this->appDir}/horizon_supervisor.ini";
        } else {
            $this->host->putFile($legacy, "[program:horizon]\ncommand=php {$this->appDir}/artisan horizon\nuser=root\n");
        }

        $report = workerReport();

        expect(reportCheck($report, 'supervisor_config')['status'])->toBe(WorkerCheckStatus::Fail)
            ->and(reportCheck($report, 'supervisor_config')['message'])->toContain("Old Supervisor config {$legacy} still runs this install")
            ->and($report['state'])->toBe('mixed');
    })->with(['symlink', 'regular file']);

    test('rConfig PRO\'s old Supervisor file is ignored', function () {
        $legacy = '/etc/supervisord.d/horizon_supervisor.ini';
        $this->host->putFile($legacy, "[program:horizon]\ncommand=php /var/www/html/rconfig8/artisan horizon\nuser=apache\n");
        $this->host->pathStats['/etc/supervisord.d/horizon_supervisor.conf'] = ['uid' => 0, 'gid' => 0, 'mode' => 0777, 'type' => 'link'];
        $this->host->resolvedPaths['/etc/supervisord.d/horizon_supervisor.conf'] = '/var/www/html/rconfig8/horizon_supervisor.ini';

        expect(reportCheck(workerReport(), 'supervisor_config')['status'])->toBe(WorkerCheckStatus::Ok);
    });

    // --- Overall ---

    test('a box with nothing in place and no Horizon is not set up', function () {
        $this->host->processList = [];
        $this->host->pathStats = [];

        $report = workerReport();

        expect($report['state'])->toBe('not_set_up')
            ->and($report['summary'])->toBe('Not set up')
            ->and($report['status'])->toBe(WorkerCheckStatus::Fail);
    });

    test('Docker skips every check', function () {
        $this->host = $this->fakeHealthyWorkerBox(isRoot: true, isDocker: true);
        $this->host->processList = [['pid' => 1, 'uid' => 0, 'arguments' => ['php', "{$this->appDir}/artisan", 'horizon']]];

        $report = workerReport();

        expect($report['state'])->toBe('docker')
            ->and($report['status'])->toBe(WorkerCheckStatus::Skipped)
            ->and($report['checks'])->toBe([])
            ->and($this->host->crontabReads)->toBe([]);
    });
});
