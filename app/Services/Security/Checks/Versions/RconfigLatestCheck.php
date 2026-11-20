<?php

namespace App\Services\Security\Checks\Versions;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\Utilities\VersionCheckService;

/**
 * Reads the stored result of the weekly update check; never calls out itself.
 */
class RconfigLatestCheck extends AbstractSecurityCheck
{
    protected string $id = 'versions.rconfig_latest';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Versions;
    protected string $title = 'rConfig is up to date';
    protected SecuritySeverity $severity = SecuritySeverity::Medium;
    protected string $rationale = 'Many security fixes are code changes that no setting can check. Running the latest rConfig Core release is the only way to have them.';
    protected array $references = ['NIST SI-2', 'NIS2 21(2)(e)', 'CIS Controls 7.3'];

    public function __construct(private readonly VersionCheckService $versions) {}

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $status = $this->versions->storedStatus();

        if ($status === null || $status['latest_version'] === null) {
            return SecurityCheckResult::skip('No update check has completed yet. Open Settings > Update and select Recheck.');
        }

        $evidence = ['installed' => $status['current_version'], 'latest' => $status['latest_version'], 'last_checked' => $status['last_checked_at']];

        if ($status['update_available']) {
            return SecurityCheckResult::warn(
                "rConfig {$status['current_version']} is behind the latest release, {$status['latest_version']}.",
                $this->fixGuide(),
                $evidence,
            );
        }

        return SecurityCheckResult::pass("rConfig {$status['current_version']} is the latest release.", $evidence);
    }

    public function fixGuide(): string
    {
        return 'Upgrade rConfig: run sudo ./update.sh from ' . rconfig_appdir_path() . ' (see Settings > Update).';
    }
}
