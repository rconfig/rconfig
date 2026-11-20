<?php

namespace App\Services\Security\Checks\Web;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\Probes\ProbeResult;
use App\Services\Security\Probes\WebProber;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

/**
 * POST /register must not reach a handler: 2xx, 419 or 422 mean the route exists. Redirects are ignored.
 */
class RegistrationClosedCheck extends AbstractSecurityCheck
{
    protected string $id = 'web.registration_closed';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Web;
    protected string $title = 'Self-registration is closed';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'rConfig accounts are created by administrators or through SSO only. An open registration route would let anyone create an account.';
    protected array $references = ['NIST AC-2', 'NIS2 21(2)(i)'];

    public function __construct(private readonly WebProber $prober) {}

    public function appliesTo(SecurityContext $context): bool
    {
        return $context->probesEnabled;
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if ($this->prober->liveTargets() === []) {
            return SecurityCheckResult::skip('No web endpoint on this server answered the probe.');
        }

        $open = array_values(array_filter(
            $this->prober->probe('/register', null, 'POST'),
            fn (ProbeResult $result): bool => in_array($result->status, [200, 201, 419, 422], true),
        ));

        if ($open === []) {
            return SecurityCheckResult::pass('POST /register is refused on every address probed.');
        }

        $responses = array_map(fn (ProbeResult $result): string => $result->describe(), array_slice($open, 0, 5));

        return SecurityCheckResult::fail(
            'A registration handler answered: ' . implode(', ', array_slice($responses, 0, 3)) . '.',
            $this->fixGuide(),
            ['responses' => $responses],
        );
    }

    public function fixGuide(): string
    {
        return 'Upgrade rConfig, then ' . lcfirst($this->artisan('route:clear')) . '. Registration must stay disabled in routes/web.php.';
    }
}
