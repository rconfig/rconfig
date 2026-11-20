<?php

namespace App\Services\Security\Checks\Tls;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\Probes\TlsInspector;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\System\SelfSignedSslService;

class CertificateExpiryCheck extends AbstractSecurityCheck
{
    protected string $id = 'tls.cert_expiry';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Tls;
    protected string $title = 'The TLS certificate is not about to expire';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'An expired certificate trains users to click through browser warnings and breaks API clients that verify TLS.';
    protected array $references = ['NIST SC-17', 'NIS2 21(2)(h)'];

    public function __construct(private readonly TlsInspector $tls) {}

    public function appliesTo(SecurityContext $context): bool
    {
        return $context->probesEnabled;
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $certificate = $this->tls->certificate();

        if ($certificate === null) {
            $target = $this->tls->target();

            return SecurityCheckResult::skip("No HTTPS listener answered on {$target['host']}:{$target['port']}.");
        }

        $days = $certificate['days_left'];
        $expires = date('Y-m-d', $certificate['valid_to']);
        $evidence = ['expires' => $expires, 'days_left' => $days];
        $fix = $this->fixGuide();

        if ($days < 0) {
            return SecurityCheckResult::fail("The certificate expired on {$expires}.", $fix, $evidence);
        }

        if ($days < (int) config('security-checks.thresholds.tls_expiry_fail_days', 14)) {
            return SecurityCheckResult::fail('The certificate expires in ' . $this->plural($days, 'day') . " ({$expires}).", $fix, $evidence);
        }

        if ($days < (int) config('security-checks.thresholds.tls_expiry_warn_days', 30)) {
            return SecurityCheckResult::warn('The certificate expires in ' . $this->plural($days, 'day') . " ({$expires}).", $fix, $evidence);
        }

        return SecurityCheckResult::pass("The certificate is valid until {$expires} ({$days} days).", $evidence);
    }

    public function fixGuide(): string
    {
        return 'Renew or replace the certificate in the Apache SSL site, then reload Apache. For a self-signed certificate, run: ' . SelfSignedSslService::COMMAND . ' --force';
    }
}
