<?php

namespace App\Services\Security\Checks\Accounts;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Http\Controllers\Auth\LoginController;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use ReflectionClass;

/**
 * Regression guard: sign-in must lock out after repeated failures.
 */
class LoginThrottleCheck extends AbstractSecurityCheck
{
    protected string $id = 'accounts.login_throttle';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Accounts;
    protected string $title = 'Repeated failed sign-ins are locked out';
    protected SecuritySeverity $severity = SecuritySeverity::Medium;
    protected string $rationale = 'Without a lockout, passwords can be guessed as fast as the server answers.';
    protected array $references = ['NIST AC-7', 'OWASP ASVS V2.2'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if (! method_exists(LoginController::class, 'hasTooManyLoginAttempts')) {
            return SecurityCheckResult::fail('The sign-in controller has no lockout.', 'Upgrade rConfig: this is a code regression.');
        }

        $controller = (new ReflectionClass(LoginController::class))->newInstanceWithoutConstructor();
        $attempts = (int) $controller->maxAttempts();
        $minutes = (int) $controller->decayMinutes();
        $evidence = ['max_attempts' => $attempts, 'decay_minutes' => $minutes];

        if ($attempts > (int) config('security-checks.thresholds.max_login_attempts', 10)) {
            return SecurityCheckResult::warn("Sign-in locks out only after {$attempts} failures.", 'Lower maxAttempts in the LoginController.', $evidence);
        }

        return SecurityCheckResult::pass('Sign-in locks out for ' . $this->plural($minutes, 'minute') . " after {$attempts} failed attempts.", $evidence);
    }

    public function fixGuide(): string
    {
        return 'Upgrade rConfig if the lockout is missing. If maxAttempts was raised in the LoginController, set it back to 10 or fewer.';
    }
}
