<?php

namespace App\Services\ConfigCompare;

use App\Models\Config;
use Illuminate\Support\Facades\Log;

class ConfigVersionSetter
{
    public function __construct(private Config $model) {}

    public function setConfigVersionTo1(): void
    {
        try {
            // latest_version is already set by the download save path; we only own config_version here.
            $this->model->config_version = 1;
            $this->model->save();
        } catch (\Throwable $e) {
            $msg = 'Error saving config version to 1: ' . $e->getMessage();
            Log::error($msg);
            throw new \RuntimeException($msg);
        }
    }

    public function setConfigVersionSameAsPrevious(Config $latestConfig): void
    {
        try {
            $this->model->config_version = $latestConfig->config_version;
            $this->model->save();
        } catch (\Throwable $e) {
            $msg = 'Error setting config version same as previous: ' . $e->getMessage();
            Log::error($msg);
            throw new \RuntimeException($msg);
        }
    }
}
