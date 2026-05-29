<?php

namespace App\Jobs;

use App\Services\Utilities\VersionCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckForUpdateJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * The number of times the job may be attempted. Network retries are
     * handled inside the service, so a single job attempt is enough.
     */
    public int $tries = 1;

    /**
     * The number of seconds the unique lock is held, so a stuck job cannot
     * block future checks indefinitely.
     */
    public int $uniqueFor = 300;

    /**
     * Refresh the persisted version-check status from GitHub.
     */
    public function handle(VersionCheckService $versionCheck): void
    {
        $versionCheck->refresh();
    }
}
