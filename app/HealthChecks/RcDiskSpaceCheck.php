<?php

namespace App\HealthChecks;

use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;

class RcDiskSpaceCheck extends UsedDiskSpaceCheck
{
    protected function getDiskUsagePercentage(): int
    {
        $process = \Symfony\Component\Process\Process::fromShellCommandline(
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
