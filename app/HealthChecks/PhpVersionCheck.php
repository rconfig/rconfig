<?php

namespace App\HealthChecks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Warns on the dashboard while PHP is older than the minimum this release
 * supports (rConfig.min_php_version). It only ever warns: rConfig keeps
 * running on an older PHP, so the operator can plan the PHP upgrade.
 *
 * Health checks run from the scheduler, so this reports the PHP the CLI and
 * the queue workers use.
 */
class PhpVersionCheck extends Check
{
    protected ?string $phpVersion = null;

    /**
     * Overrides the PHP version under check, for tests.
     */
    public function phpVersion(string $version): static
    {
        $this->phpVersion = $version;

        return $this;
    }

    public function run(): Result
    {
        $current = $this->phpVersion ?? PHP_VERSION;
        $minimum = (string) config('rConfig.min_php_version');

        $result = Result::make()->meta([
            'php_version' => $current,
            'minimum' => $minimum,
        ]);

        if (version_compare($current, $minimum, '>=')) {
            return $result
                ->ok("PHP {$current} meets the minimum of {$minimum}.")
                ->shortSummary($current);
        }

        return $result
            ->warning("PHP {$current} is older than {$minimum}, the minimum for this rConfig release. Upgrade PHP to {$minimum} or later.")
            ->shortSummary('Upgrade PHP');
    }
}
