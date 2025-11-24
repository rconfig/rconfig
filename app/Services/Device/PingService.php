<?php

namespace App\Services\Device;

class PingService
{
    public function check(string $ip): bool
    {
        // Fast ping with timeout (adjust to suit)
        $command = sprintf('ping -c 1 -W 1 %s', escapeshellarg($ip));
        exec($command, $output, $resultCode);

        return $resultCode === 0;
    }
}
