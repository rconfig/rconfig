<?php

namespace App\Services\Security\Checks\App;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\System\SelfSignedSslService;

class AppUrlHttpsCheck extends AbstractSecurityCheck
{
    protected string $id = 'app.url_https';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = 'rConfig is served over HTTPS';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Over plain HTTP, sign-in passwords, session cookies, API tokens and device credentials cross the network unencrypted.';
    protected array $references = ['NIST SC-8', 'NIS2 21(2)(h)', 'OWASP ASVS V9.1'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if (SelfSignedSslService::appUsesHttps()) {
            return SecurityCheckResult::pass('APP_URL uses https://.');
        }

        $fix = $context->isDocker ? self::DOCKER_FIX : $this->serverFix();

        return SecurityCheckResult::fail('APP_URL uses http://, so passwords and device credentials cross the network unencrypted.', $fix);
    }

    public function fixGuide(): string
    {
        return $this->serverFix() . ' In Docker: ' . lcfirst(self::DOCKER_FIX);
    }

    private const DOCKER_FIX = 'Terminate TLS on your reverse proxy and set APP_URL to https://.';

    private function serverFix(): string
    {
        return 'Run: ' . SelfSignedSslService::COMMAND . ' for a self-signed certificate, or install your own certificate and set APP_URL to https://.';
    }
}
