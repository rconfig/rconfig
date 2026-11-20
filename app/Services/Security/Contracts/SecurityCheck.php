<?php

namespace App\Services\Security\Contracts;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

/**
 * One rconfig:security-check check. Checks are read-only and never return a secret value.
 */
interface SecurityCheck
{
    /**
     * Stable ID shared with the docs, e.g. "redis.auth".
     */
    public function id(): string;

    public function group(): SecurityCheckGroup;

    public function title(): string;

    public function severity(): SecuritySeverity;

    /**
     * False hides the check on this install (e.g. a host check inside Docker).
     */
    public function appliesTo(SecurityContext $context): bool;

    public function run(SecurityContext $context): SecurityCheckResult;

    /**
     * Standards references, e.g. ["NIST SC-8", "OWASP ASVS V9.1"].
     *
     * @return list<string>
     */
    public function references(): array;

    public function docsUrl(): string;

    /**
     * How to fix a failure or warning, for the docs check reference. Results carry the fix for the actual finding.
     */
    public function fixGuide(): string;

    /**
     * One paragraph on why the check matters, shown on the Security Review page.
     */
    public function rationale(): string;
}
