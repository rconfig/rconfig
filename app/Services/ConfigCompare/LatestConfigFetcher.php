<?php

namespace App\Services\ConfigCompare;

use App\Models\Config;

class LatestConfigFetcher
{
    public function __construct(private Config $model) {}

    /**
     * Get the latest successfully downloaded config for this device + command,
     * excluding the current one (skip 1).
     */
    public function getLatestDownloadedConfig(): ?Config
    {
        return Config::where('device_id', $this->model->device_id)
            ->where('command', $this->model->command)
            ->where('download_status', 1)
            ->orderBy('id', 'desc')
            ->skip(1)
            ->take(1)
            ->first();
    }
}
