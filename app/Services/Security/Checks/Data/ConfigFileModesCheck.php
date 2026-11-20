<?php

namespace App\Services\Security\Checks\Data;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

/**
 * Samples saved config files for modes other local users can read.
 */
class ConfigFileModesCheck extends AbstractSecurityCheck
{
    public const SAMPLE_SIZE = 500;

    protected string $id = 'data.config_file_modes';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Data;
    protected string $title = 'Saved configs are private to rConfig';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Device configs hold secrets such as VPN keys and SNMP communities. Configs saved by older releases could be readable by every local account, so they must have no access for other users.';
    protected array $references = ['NIST AC-6', 'NIST SC-28'];

    /**
     * @param  string|null  $path  config folder, overridable for tests
     */
    public function __construct(private readonly ?string $path = null) {}

    public function appliesTo(SecurityContext $context): bool
    {
        return ! $context->isDocker;
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        [$sampled, $loose] = $this->sample($this->path ?? config_data_path());

        if ($sampled === 0) {
            return SecurityCheckResult::pass('No saved configs were found yet.');
        }

        if ($loose === 0) {
            return SecurityCheckResult::pass('None of the ' . $this->plural($sampled, 'sampled config') . ' can be read by other users.', ['sampled' => $sampled]);
        }

        return SecurityCheckResult::fail(
            "{$loose} of " . $this->plural($sampled, 'sampled config') . ' can be read by any local user.',
            $this->fixGuide(),
            ['sampled' => $sampled, 'readable_by_others' => $loose],
        );
    }

    public function fixGuide(): string
    {
        return $this->artisan('rconfig:set-config-permissions') . ' (add --dry-run first to see what would change)';
    }

    /**
     * @return array{0: int, 1: int} files sampled, files readable by others
     */
    private function sample(string $path): array
    {
        if (! is_dir($path)) {
            return [0, 0];
        }

        $sampled = 0;
        $loose = 0;

        try {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);

            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                if ($item->isLink() || ! $item->isFile() || ! in_array($item->getExtension(), ['txt', 'json'], true)) {
                    continue;
                }

                $mode = @fileperms($item->getPathname());

                if ($mode !== false && ($mode & 0004) !== 0) {
                    $loose++;
                }

                if (++$sampled >= self::SAMPLE_SIZE) {
                    break;
                }
            }
        } catch (Throwable) {
            return [$sampled, $loose];
        }

        return [$sampled, $loose];
    }
}
