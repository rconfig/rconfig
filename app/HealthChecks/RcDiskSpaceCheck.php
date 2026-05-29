<?php

namespace App\HealthChecks;

use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Symfony\Component\Process\Process;

class RcDiskSpaceCheck extends UsedDiskSpaceCheck
{
    protected function getDiskUsagePercentage(): int
    {
        $process = Process::fromShellCommandline(
            'df -P ' . ($this->filesystemName ?: '.')
        );

        $process->run();

        $output = $process->getOutput();

        preg_match_all('/(\d+)%/', $output, $matches);

        if (empty($matches[1])) {
            throw new \Exception("No percentage values matched in df output:\n" . $output);
        }

        return (int) end($matches[1]);
    }
}
