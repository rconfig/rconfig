<?php

namespace App\HealthChecks;

use App\Services\System\WorkerUserCheckService;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Warns on the dashboard when a box has not been moved off root yet: Horizon
 * running as root, the scheduler running as root, or no
 * /etc/cron.d/rconfig-core. All three are fixed by running update.sh.
 *
 * The web user cannot read root's cron table, so a schedule:run line left
 * there is detected by its effect. Health checks run from schedule:run, so a
 * check running as root means the scheduler runs as root. That run leaves a marker
 * in the cache, so the check also warns when it runs as the web user while a
 * root scheduler is still firing alongside the cron.d one.
 */
class WorkerPrivilegeCheck extends Check
{
    public const CRON_FILE = '/etc/cron.d/rconfig-core';

    public const ROOT_SCHEDULER_CACHE_KEY = 'rconfig:scheduler-ran-as-root';

    /**
     * Longer than the five minute health check interval, so one missed run
     * does not clear the warning.
     */
    public const ROOT_SCHEDULER_MARKER_MINUTES = 15;

    public function __construct(
        private readonly HostFilesystem $host,
        private readonly WorkerUserCheckService $workers,
    ) {
        parent::__construct();
    }

    public function run(): Result
    {
        $result = Result::make();

        // The container runs Horizon and the scheduler itself.
        if ($this->host->isDocker()) {
            return $result->ok('Not applicable in Docker.')->shortSummary('Ok');
        }

        $problems = [];

        $rootHorizon = array_filter(
            $this->workers->runningHorizonProcesses(),
            fn (array $process): bool => $process['uid'] === 0
        );

        if ($rootHorizon !== []) {
            $problems[] = 'Horizon is running as root';
        }

        if ($this->host->isRoot()) {
            Cache::put(self::ROOT_SCHEDULER_CACHE_KEY, now()->getTimestamp(), now()->addMinutes(self::ROOT_SCHEDULER_MARKER_MINUTES));
        }

        if ($this->host->isRoot() || Cache::has(self::ROOT_SCHEDULER_CACHE_KEY)) {
            $problems[] = "The scheduler is running as root (a schedule:run line for this install is still in root's crontab)";
        }

        if (! $this->host->fileExists(self::CRON_FILE)) {
            $problems[] = 'The scheduler cron file ' . self::CRON_FILE . ' is missing';
        }

        $result->meta(['problems' => $problems]);

        if ($problems === []) {
            return $result->ok('Horizon and the scheduler run as the web user.')->shortSummary('Ok');
        }

        return $result
            ->warning(implode('. ', $problems) . '. Fix it by running sudo ./update.sh from ' . base_path() . '.')
            ->shortSummary('Run sudo ./update.sh');
    }
}
