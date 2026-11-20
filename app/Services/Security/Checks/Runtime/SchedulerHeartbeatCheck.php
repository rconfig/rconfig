<?php

namespace App\Services\Security\Checks\Runtime;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Enums\Status;

/**
 * Reads the heartbeat ScheduleCheckHeartbeatCommand writes every five minutes.
 */
class SchedulerHeartbeatCheck extends AbstractSecurityCheck
{
    protected string $id = 'runtime.scheduler';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Runtime;
    protected string $title = 'The scheduler is running';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'The scheduler runs backups, health checks and the daily security check. If it stops, problems stop being detected.';
    protected array $references = ['NIST SI-4', 'NIS2 21(2)(c)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $result = ScheduleCheck::new()->heartbeatMaxAgeInMinutes(10)->run();

        if ($result->status->equals(Status::ok())) {
            return SecurityCheckResult::pass('The scheduler ran in the last 10 minutes.');
        }

        $fix = $context->isDocker ? self::DOCKER_FIX : $this->serverFix();

        return SecurityCheckResult::fail('The scheduler has not run in the last 10 minutes.', $fix);
    }

    public function fixGuide(): string
    {
        return $this->serverFix() . ' In Docker: ' . lcfirst(self::DOCKER_FIX);
    }

    private const DOCKER_FIX = 'Restart the rConfig container, whose supervisord starts the scheduler.';

    private function serverFix(): string
    {
        return 'Run sudo ./update.sh from ' . rconfig_appdir_path() . ', which writes /etc/cron.d/rconfig-core to run the scheduler as the web user.';
    }
}
