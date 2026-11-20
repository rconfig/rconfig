<?php

namespace App\Services\Security\Checks\Runtime;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecurityCheckStatus;
use App\Enums\SecuritySeverity;
use App\Enums\WorkerCheckStatus;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\System\WorkerUserCheckService;

/**
 * Same report as rconfig:check-worker-user and the Worker Privilege health check.
 */
class WorkerUserCheck extends AbstractSecurityCheck
{
    protected string $id = 'runtime.worker_user';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Runtime;
    protected string $title = 'Nothing runs as root';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'Horizon workers run queued jobs and the scheduler runs rConfig commands. Running either as root turns any flaw in a job into full control of the server.';
    protected array $references = ['NIST AC-6', 'CIS Controls 5.4'];

    public function __construct(private readonly WorkerUserCheckService $workers) {}

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $report = $this->workers->report();

        if ($report['checks'] === []) {
            return SecurityCheckResult::skip($report['summary'] . '.');
        }

        $status = SecurityCheckStatus::fromWorkerStatus($report['status']);
        $problems = array_values(array_filter($report['checks'], fn (array $check): bool => in_array($check['status'], [WorkerCheckStatus::Warn, WorkerCheckStatus::Fail], true)));
        $evidence = ['state' => $report['state'], 'web_user' => $report['web_user'], 'checked_as_root' => $report['checked_as_root']];

        if ($problems === []) {
            return SecurityCheckResult::pass('Horizon and the scheduler run as ' . $report['web_user'] . '.', $evidence);
        }

        $fix = $report['fix'] ?? $this->fixGuide();
        $message = $report['summary'] . '. ' . implode(' ', array_column($problems, 'message'));

        return new SecurityCheckResult($status === SecurityCheckStatus::Pass ? SecurityCheckStatus::Warn : $status, $message, $fix, $evidence);
    }

    public function fixGuide(): string
    {
        return 'Run sudo ./update.sh from ' . rconfig_appdir_path() . ', then ' . $this->artisanCommand('rconfig:check-worker-user', true);
    }
}
