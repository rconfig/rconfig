<?php

namespace App\Services\ConfigCompare;

use App\Models\Config;
use Illuminate\Support\Facades\Log;

class ConfigHasher
{
    /**
     * Calculate the sha1 hash of a (cleaned) config file and persist it on the model.
     */
    public function hashAndSaveConfig(Config $config, string $filePath): void
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            Log::error("ConfigHasher: file not accessible: {$filePath}");
            throw new \RuntimeException("File not accessible: {$filePath}");
        }

        $configHash = sha1_file($filePath);

        if ($configHash === false) {
            Log::error("ConfigHasher: failed to calculate hash for file: {$filePath}");
            throw new \RuntimeException("Hash calculation failed for file: {$filePath}");
        }

        $config->config_hash = $configHash;
        $config->save();
    }
}
