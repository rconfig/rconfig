<?php

namespace App\Services\Security\Checks\App;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

class AppEnvCheck extends AbstractSecurityCheck
{
    protected string $id = 'app.env';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = 'Environment is production';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Laravel relaxes several protections outside production. In the local environment the Horizon dashboard opens to anyone, without its sign-in gate.';
    protected array $references = ['NIST CM-6', 'CIS Controls 4.1'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $environment = (string) config('app.env');
        $fix = $this->fixGuide();

        if ($environment === 'production') {
            return SecurityCheckResult::pass('APP_ENV is production.', ['environment' => $environment]);
        }

        if ($environment === 'local') {
            return SecurityCheckResult::fail('APP_ENV is local, which opens the Horizon dashboard without sign-in.', $fix, ['environment' => $environment]);
        }

        return SecurityCheckResult::warn("APP_ENV is {$environment}, not production.", $fix, ['environment' => $environment]);
    }

    public function fixGuide(): string
    {
        return 'Set APP_ENV=production in .env, then ' . lcfirst($this->artisan('config:cache'));
    }
}
