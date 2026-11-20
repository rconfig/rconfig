<?php

namespace App\Services\Security\Checks\App;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

class AppDebugCheck extends AbstractSecurityCheck
{
    protected string $id = 'app.debug';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = 'Debug mode is off';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'With APP_DEBUG on, error pages show stack traces, file paths, environment values and database details to whoever triggered them, including people who are not signed in.';
    protected array $references = ['NIST SI-11', 'OWASP ASVS V14.3', 'NIS2 21(2)(e)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if (! (bool) config('app.debug')) {
            return SecurityCheckResult::pass('APP_DEBUG is off.');
        }

        return SecurityCheckResult::fail('APP_DEBUG is on, so error pages show stack traces and configuration details.', $this->fixGuide());
    }

    public function fixGuide(): string
    {
        return 'Turn debugging off under Settings > Logging & Debugging, or set APP_DEBUG=false in .env, then ' . lcfirst($this->artisan('config:cache'));
    }
}
