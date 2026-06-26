<?php

namespace App\Services\ConfigCompare;

use App\Models\Command;
use App\Models\Config;
use Illuminate\Support\Facades\Log;

class ConfigVersionCompareService
{
    private ?Command $command;
    private ?Config $latestConfig;

    public function __construct(private Config $model, private string $commandName)
    {
        $this->command = (new CommandFetcher($model, $commandName))->getCommand();
        $this->latestConfig = (new LatestConfigFetcher($model))->getLatestDownloadedConfig();
    }

    /**
     * Determine the version of a newly downloaded config and, when its cleaned
     * content differs from the previous version, record a ConfigChange diff.
     */
    public function version_compare(): bool
    {
        try {
            if ($this->command === null) {
                return false;
            }

            if ($this->isConfigFileInvalid()) {
                $this->logConfigFileIssue();

                return true;
            }

            if ($this->isFirstConfigVersion()) {
                (new ConfigVersionSetter($this->model))->setConfigVersionTo1();

                return true;
            }

            $cleanedConfigs = $this->getCleanedConfigFiles();
            $this->updateConfigHashes($cleanedConfigs);

            if ($this->configsAreIdentical()) {
                (new ConfigVersionSetter($this->model))->setConfigVersionSameAsPrevious($this->latestConfig);

                return true;
            }

            return $this->handleChangedConfigs($cleanedConfigs);
        } catch (\Throwable $e) {
            Log::error('Error in version_compare: ' . $e->getMessage());

            return true;
        }
    }

    private function isConfigFileInvalid(): bool
    {
        $configLocation = $this->model->config_location;

        return empty($configLocation)
            || ! file_exists($configLocation)
            || filesize($configLocation) === 0;
    }

    private function logConfigFileIssue(): void
    {
        activityLogIt(
            __CLASS__,
            __FUNCTION__,
            'info',
            'Config file is empty or does not exist',
            'config_compare',
            $this->model->device_name,
            $this->model->device_id,
            $this->model->device_category,
            $this->model->id
        );
    }

    private function isFirstConfigVersion(): bool
    {
        return ! $this->latestConfig || $this->latestConfig->config_version === null;
    }

    /**
     * @return array{old: string, new: string}
     */
    private function getCleanedConfigFiles(): array
    {
        return [
            'old' => (new ConfigCompareExclusionCleaner($this->latestConfig->config_location, $this->commandName))->excludeLines(),
            'new' => (new ConfigCompareExclusionCleaner($this->model->config_location, $this->commandName))->excludeLines(),
        ];
    }

    /**
     * @param  array{old: string, new: string}  $cleanedConfigs
     */
    private function updateConfigHashes(array $cleanedConfigs): void
    {
        $hasher = new ConfigHasher;
        $hasher->hashAndSaveConfig($this->latestConfig, $cleanedConfigs['old']);
        $hasher->hashAndSaveConfig($this->model, $cleanedConfigs['new']);
    }

    private function configsAreIdentical(): bool
    {
        return $this->latestConfig->config_hash === $this->model->config_hash;
    }

    /**
     * @param  array{old: string, new: string}  $cleanedConfigs
     */
    private function handleChangedConfigs(array $cleanedConfigs): bool
    {
        // Precompute the next version so the ConfigChange record uses the correct value.
        $this->model->config_version = ($this->latestConfig->config_version ?? 0) + 1;

        $comparisonResult = (new ConfigComparer)->compare(
            $cleanedConfigs['old'],
            $cleanedConfigs['new'],
            $this->model,
            $this->latestConfig
        );

        // If exclusions removed every difference, keep the previous version number.
        if (($comparisonResult['diff_type'] ?? null) === 'none') {
            (new ConfigVersionSetter($this->model))->setConfigVersionSameAsPrevious($this->latestConfig);

            return true;
        }

        // Real diff: persist the bumped version (latest_version is owned by the save path).
        $this->model->save();

        (new ConfigCompareLogService($this->model, $this->command))->logAndNotify($comparisonResult['diff'] ?? '');

        return true;
    }
}
