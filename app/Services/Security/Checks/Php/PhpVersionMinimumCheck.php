<?php

namespace App\Services\Security\Checks\Php;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\Probes\PhpRuntimeInspector;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

class PhpVersionMinimumCheck extends AbstractSecurityCheck
{
    protected string $id = 'php.version_min';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Php;
    protected string $title = 'PHP meets rConfig\'s minimum version';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'rConfig\'s PHP minimum tracks PHP security releases. Older PHP builds carry known flaws that PHP has already fixed.';
    protected array $references = ['NIST SI-2', 'NIS2 21(2)(e)', 'CIS Controls 7.3'];

    public function __construct(private readonly PhpRuntimeInspector $php) {}

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $installed = $this->php->snapshot()['version'] ?: PHP_VERSION;
        $minimum = (string) config('rConfig.min_php_version');
        $evidence = ['installed' => $installed, 'minimum' => $minimum, 'source' => $this->php->source()];

        if (version_compare($installed, $minimum, '>=')) {
            return SecurityCheckResult::pass("PHP {$installed} meets the {$minimum} minimum.", $evidence);
        }

        return SecurityCheckResult::warn(
            "PHP {$installed} is below rConfig's minimum of {$minimum}.",
            $this->fixGuide(),
            $evidence,
        );
    }

    public function fixGuide(): string
    {
        return 'Update PHP through the server\'s package updates (sudo dnf upgrade \'php*\' or sudo apt upgrade \'php8.*\'), then restart PHP-FPM, Apache and Horizon.';
    }
}
