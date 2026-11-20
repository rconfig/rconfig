<?php

namespace App\Services\Security\Checks\App;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\Utilities\HostFilesystem;

/**
 * .env holds every secret, so other local users must not read it.
 */
class EnvFilePermissionsCheck extends AbstractSecurityCheck
{
    protected string $id = 'app.env_file_perms';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = '.env is not readable by other local users';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = '.env holds the database password, APP_KEY and the Redis password. Any local account that can read it can decrypt every stored credential.';
    protected array $references = ['NIST AC-6', 'NIST SC-28', 'NIS2 21(2)(i)'];

    public function __construct(private readonly HostFilesystem $host) {}

    public function appliesTo(SecurityContext $context): bool
    {
        return ! $context->isDocker;
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $path = rconfig_appdir_path() . '/.env';
        $path = $this->host->resolvePath($path) ?? $path;
        $stat = $this->host->linkStat($path);

        if ($stat === null) {
            return SecurityCheckResult::skip("{$path} was not found or could not be read.");
        }

        $mode = $stat['mode'];
        $octal = sprintf('%04o', $mode);
        $evidence = ['path' => $path, 'mode' => $octal, 'owner' => $this->host->userName($stat['uid'])];
        $fix = "Run: sudo chmod o-rwx {$path}";

        if (($mode & 0002) !== 0) {
            return SecurityCheckResult::fail("{$path} is {$octal}, so any local user can change it.", $fix, $evidence);
        }

        if (($mode & 0004) !== 0) {
            return SecurityCheckResult::fail("{$path} is {$octal}, so any local user can read every rConfig secret.", $fix, $evidence);
        }

        return SecurityCheckResult::pass("{$path} is {$octal}, with no access for other users.", $evidence);
    }

    public function fixGuide(): string
    {
        return 'Run: sudo chmod o-rwx ' . rconfig_appdir_path() . '/.env';
    }
}
