<?php

namespace App\Services\Security\Checks\Os;

use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Unprivileged systemd queries for the OS checks.
 */
class SystemServices
{
    /** @var array<string, string> */
    private array $states = [];

    /**
     * "active", "inactive", "failed", "activating" or "unknown" (unit missing or no systemd).
     */
    public function state(string $unit): string
    {
        if (isset($this->states[$unit])) {
            return $this->states[$unit];
        }

        try {
            $state = trim(Process::timeout(5)->run(['systemctl', 'is-active', $unit])->output()) ?: 'unknown';
        } catch (Throwable) {
            $state = 'unknown';
        }

        return $this->states[$unit] = in_array($state, ['active', 'inactive', 'failed', 'activating'], true) ? $state : 'unknown';
    }

    /**
     * @param  list<string>  $units
     */
    public function anyActive(array $units): ?string
    {
        foreach ($units as $unit) {
            if ($this->state($unit) === 'active') {
                return $unit;
            }
        }

        return null;
    }

    /**
     * True when systemctl answered for at least one unit, so "not active" means something.
     *
     * @param  list<string>  $units
     */
    public function knows(array $units): bool
    {
        foreach ($units as $unit) {
            if ($this->state($unit) !== 'unknown') {
                return true;
            }
        }

        return false;
    }
}
