<?php

namespace App\HealthChecks;

use App\Enums\WorkerCheckStatus;
use App\Services\System\WorkerUserCheckService;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Warns on the dashboard when a box has not been moved off root yet. It shows
 * the same report as rconfig:check-worker-user, so the two never disagree:
 * ok only when every check is OK or skipped, otherwise a warning listing each
 * WARN and FAIL. All of them are fixed by running update.sh.
 *
 * The web user cannot read root's cron table, so a schedule:run line left
 * there is detected by its effect. Health checks run from schedule:run, so a
 * check running as root means the scheduler runs as root. That run leaves a
 * marker in the cache, which the report turns into a warning, so it also shows
 * when the check later runs as the web user while a root scheduler still fires
 * alongside the cron.d one.
 */
class WorkerPrivilegeCheck extends Check
{
    public const CRON_FILE = WorkerUserCheckService::CRON_FILE;

    public const ROOT_SCHEDULER_CACHE_KEY = WorkerUserCheckService::ROOT_SCHEDULER_CACHE_KEY;

    public const ROOT_SCHEDULER_MARKER_MINUTES = WorkerUserCheckService::ROOT_SCHEDULER_MARKER_MINUTES;

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

        if ($this->host->isRoot()) {
            Cache::put(self::ROOT_SCHEDULER_CACHE_KEY, now()->getTimestamp(), now()->addMinutes(self::ROOT_SCHEDULER_MARKER_MINUTES));
        }

        $report = $this->workers->report();

        $problems = array_values(array_map(
            fn (array $check): string => $check['message'],
            array_filter($report['checks'], fn (array $check): bool => in_array($check['status'], [WorkerCheckStatus::Warn, WorkerCheckStatus::Fail], true)),
        ));

        $result->meta(['problems' => $problems, 'state' => $report['state']]);

        if ($problems === []) {
            return $result->ok('Horizon and the scheduler run as the web user.')->shortSummary('Ok');
        }

        if ($report['fix'] === null) {
            return $result->warning(implode(' ', $problems))->shortSummary('Check the worker user');
        }

        return $result
            ->warning($report['summary'] . '. ' . implode(' ', $problems) . ' Fix it by running sudo ./update.sh from ' . base_path() . '.')
            ->shortSummary('Run sudo ./update.sh');
    }
}
