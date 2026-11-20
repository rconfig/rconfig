<?php

namespace App\Services\Security\Checks\Runtime;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Spatie\Health\Checks\Checks\HorizonCheck;
use Spatie\Health\Enums\Status;

class QueueWorkersCheck extends AbstractSecurityCheck
{
    protected string $id = 'runtime.queue';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Runtime;
    protected string $title = 'Queue workers are running';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Horizon runs config downloads, notifications (security alerts included) and the daily security check. Without it, they are queued but never run.';
    protected array $references = ['NIST SI-4', 'NIS2 21(2)(c)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $result = HorizonCheck::new()->run();

        if ($result->status->equals(Status::ok())) {
            return SecurityCheckResult::pass('Horizon is running.');
        }

        $fix = $context->isDocker ? self::DOCKER_FIX : self::SERVER_FIX;

        return SecurityCheckResult::fail('Horizon is not running: ' . rtrim($result->getNotificationMessage(), '.') . '.', $fix);
    }

    public function fixGuide(): string
    {
        return self::SERVER_FIX . '. In Docker: ' . lcfirst(self::DOCKER_FIX);
    }

    private const SERVER_FIX = 'Start it under Supervisor as the web user: sudo supervisorctl start rconfig-core-horizon';

    private const DOCKER_FIX = 'Restart the rConfig container, whose supervisord starts Horizon.';
}
