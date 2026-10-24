<?php

namespace App\Services\System;

use App\Services\Utilities\HostFilesystem;

/**
 * Finds this install's running Horizon master and the OS user it runs as, so
 * the dashboard (and rconfig:check-worker-user) can flag a worker that is not
 * the web user. Only processes that run this app folder's artisan count, so
 * rConfig PRO's Horizon on a shared box is never mistaken for Core's.
 */
class WorkerUserCheckService
{
    public function __construct(private readonly HostFilesystem $host) {}

    /**
     * The OS user the web server, and so Horizon, is expected to run as.
     *
     * @return array{0: string, 1: string}
     */
    public function expectedUser(): array
    {
        return rconfig_web_user();
    }

    /**
     * Running Horizon master processes for this install. Matches
     * "<this app>/artisan horizon" only, never horizon:work, horizon:snapshot
     * or another app folder's artisan.
     *
     * @return list<array{pid: int, uid: int, user: string, command: string}>
     */
    public function runningHorizonProcesses(): array
    {
        $artisanPaths = array_unique(array_filter([
            base_path('artisan'),
            realpath(base_path('artisan')) ?: null,
        ]));

        $matches = [];

        foreach ($this->host->processes() as $process) {
            $arguments = $process['arguments'];

            foreach ($arguments as $index => $argument) {
                if (in_array($argument, $artisanPaths, true) && ($arguments[$index + 1] ?? null) === 'horizon') {
                    $matches[] = [
                        'pid' => $process['pid'],
                        'uid' => $process['uid'],
                        'user' => $this->host->userName($process['uid']) ?? (string) $process['uid'],
                        'command' => implode(' ', $arguments),
                    ];

                    break;
                }
            }
        }

        return $matches;
    }
}
