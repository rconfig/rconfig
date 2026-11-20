<?php

namespace App\Services\Security\Checks\Php;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\Probes\PhpRuntimeInspector;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

/**
 * Reads the web server's PHP where it can; evidence "source" and "sapi" say which PHP was read.
 */
class DisplayErrorsCheck extends AbstractSecurityCheck
{
    /**
     * ini key => expected state.
     *
     * @var array<string, bool>
     */
    private const EXPECTED = ['display_errors' => false, 'display_startup_errors' => false, 'log_errors' => true];

    protected string $id = 'php.display_errors';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Php;
    protected string $title = 'PHP errors are logged, not shown';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Errors printed to the page reveal file paths, queries and values to whoever triggered them. They belong in the log only.';
    protected array $references = ['NIST SI-11', 'OWASP ASVS V7.4'];

    public function __construct(private readonly PhpRuntimeInspector $php) {}

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $wrong = [];
        $evidence = ['source' => $this->php->source(), 'sapi' => $this->php->sapi()];

        foreach (self::EXPECTED as $key => $expected) {
            $actual = $this->php->enabled($key);
            $evidence[$key] = $actual ? 'On' : 'Off';

            if ($actual !== $expected) {
                $wrong[] = $key . ' = ' . ($expected ? 'On' : 'Off');
            }
        }

        if ($wrong === []) {
            return SecurityCheckResult::pass('php.ini is set as recommended' . $this->php->sourceNote() . '.', $evidence);
        }

        return SecurityCheckResult::warn(
            'php.ini should set ' . implode(', ', $wrong) . $this->php->sourceNote() . '.',
            'Set ' . implode(', ', $wrong) . ' in php.ini (e.g. /etc/php.ini or /etc/php/8.4/fpm/php.ini), then restart PHP-FPM or Apache.',
            $evidence,
        );
    }

    public function fixGuide(): string
    {
        return 'Set display_errors = Off, display_startup_errors = Off and log_errors = On in php.ini (e.g. /etc/php.ini or /etc/php/8.4/fpm/php.ini), then restart PHP-FPM or Apache.';
    }
}
