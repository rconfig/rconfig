<?php

namespace App\Services\DataPurge;

use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\ConfigSummary;
use App\Models\SettingsPurge;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;

/**
 * Retention for the configs target: old config versions, the config_changes rows that reference
 * them, and failed downloads.
 */
class ConfigRetentionService
{
    private const DEFAULT_BATCH_SIZE = 1000;

    private const DEFAULT_MIN_VERSIONS = 2;

    /** @var array<int, true> */
    private array $affectedDeviceIds = [];

    public function runFromSettings(SettingsPurge $setting, bool $dryRun = false): ConfigRetentionReport
    {
        $options = $setting->options ?? [];
        $report = new ConfigRetentionReport;
        $this->affectedDeviceIds = [];

        if (! $setting->isEnabled() || (int) $setting->retention_days === 0) {
            return $report;
        }

        $minVersionsPerDeviceCommand = max(1, (int) ($options['min_versions_per_device_command'] ?? self::DEFAULT_MIN_VERSIONS));
        $batchSize = max(1, (int) ($options['batch_size'] ?? self::DEFAULT_BATCH_SIZE));
        $mainCutoff = Carbon::now()->subDays($setting->retention_days);

        $this->collectDownloadedConfigsToDelete($report, $mainCutoff, $minVersionsPerDeviceCommand);
        $this->collectConfigChangesToDelete($report, $batchSize);

        if (isset($options['non_downloaded_retention_days'])) {
            $this->collectNonDownloadedConfigsToDelete($report, Carbon::now()->subDays((int) $options['non_downloaded_retention_days']));
        }

        if ($dryRun) {
            return $report;
        }

        $this->deleteConfigChanges($report, $batchSize);
        $report->totalConfigsDeleted = $this->deleteConfigs($report, $report->configIdsToDelete, $batchSize);
        $report->totalNonDownloadedConfigsDeleted = $this->deleteConfigs($report, $report->nonDownloadedConfigIdsToDelete, $batchSize);
        $this->resyncConfigSummaries();

        return $report;
    }

    /**
     * Per device and command, keep the latest version and the newest N versions whatever their age,
     * and mark the rest for deletion once they are older than the cutoff.
     */
    private function collectDownloadedConfigsToDelete(ConfigRetentionReport $report, Carbon $mainCutoff, int $minVersionsPerDeviceCommand): void
    {
        $groups = $this->purgeableConfigs()
            ->where('download_status', '!=', 0)
            ->select('device_id', 'command')
            ->groupBy('device_id', 'command')
            ->get();

        foreach ($groups as $group) {
            $configs = $this->purgeableConfigs()
                ->where('download_status', '!=', 0)
                ->where('device_id', $group->device_id)
                ->where('command', $group->command)
                ->orderBy('config_version')
                ->orderBy('id')
                ->get(['id', 'device_id', 'config_version', 'latest_version', 'end_time']);

            if ($configs->isEmpty()) {
                continue;
            }

            $report->totalConfigsConsidered += $configs->count();

            $newestFirst = $configs->sortByDesc(fn (Config $config): array => [(int) $config->config_version, $config->id])->values();
            $latestRow = $configs->firstWhere('latest_version', 1) ?? $newestFirst->first();

            $protectedIds = $newestFirst->take($minVersionsPerDeviceCommand)->pluck('id')->push($latestRow->id)->unique()->all();

            foreach ($configs as $config) {
                if (in_array($config->id, $protectedIds, true) || $config->end_time === null) {
                    continue;
                }

                if (Carbon::parse($config->end_time)->lt($mainCutoff)) {
                    $report->configIdsToDelete[] = $config->id;
                    $this->affectedDeviceIds[(int) $config->device_id] = true;
                }
            }
        }

        $report->configIdsToDelete = array_values(array_unique($report->configIdsToDelete));
        $report->totalConfigsToDelete = count($report->configIdsToDelete);
    }

    private function collectConfigChangesToDelete(ConfigRetentionReport $report, int $batchSize): void
    {
        $changeIds = [];

        foreach (array_chunk($report->configIdsToDelete, $batchSize) as $idChunk) {
            $ids = ConfigChange::query()
                ->toBase()
                ->whereIn('current_config_id', $idChunk)
                ->orWhereIn('previous_config_id', $idChunk)
                ->pluck('id');

            foreach ($ids as $changeId) {
                $changeIds[(int) $changeId] = true;
            }
        }

        $report->configChangeIdsToDelete = array_keys($changeIds);
        $report->totalConfigChangesToDelete = count($report->configChangeIdsToDelete);
    }

    /**
     * Failed downloads have their own cutoff and no minimum-versions protection.
     */
    private function collectNonDownloadedConfigsToDelete(ConfigRetentionReport $report, Carbon $cutoff): void
    {
        $rows = $this->purgeableConfigs()
            ->where('download_status', 0)
            ->where('end_time', '<', $cutoff)
            ->get(['id', 'device_id']);

        foreach ($rows as $row) {
            $this->affectedDeviceIds[(int) $row->device_id] = true;
        }

        $report->nonDownloadedConfigIdsToDelete = $rows->pluck('id')->all();
        $report->totalNonDownloadedConfigsToDelete = count($report->nonDownloadedConfigIdsToDelete);
    }

    /**
     * Configs of enabled devices, plus orphans whose device no longer exists. Disabled devices are left alone.
     */
    private function purgeableConfigs(): Builder
    {
        return Config::query()->where(function (Builder $query): void {
            $query->whereHas('device', fn (Builder $device): Builder => $device->notDisabled())
                ->orWhereDoesntHave('device');
        });
    }

    /**
     * Version numbers are deliberately not resequenced: a history that now starts at a higher
     * number is expected after retention, and renumbering would rewrite versions already shown.
     */
    private function deleteConfigChanges(ConfigRetentionReport $report, int $batchSize): void
    {
        foreach (array_chunk($report->configChangeIdsToDelete, $batchSize) as $idChunk) {
            $report->totalConfigChangesDeleted += ConfigChange::query()->whereIn('id', $idChunk)->delete();
        }
    }

    /**
     * Bulk delete rows, then their files. Rows go first so a file is only removed once no remaining row points at it.
     *
     * @param  list<int>  $ids
     */
    private function deleteConfigs(ConfigRetentionReport $report, array $ids, int $batchSize): int
    {
        $deleted = 0;

        foreach (array_chunk($ids, $batchSize) as $idChunk) {
            $configs = Config::query()->whereIn('id', $idChunk)->get(['id', 'device_id', 'command', 'config_location']);

            $deleted += Config::query()->whereIn('id', $idChunk)->delete();

            foreach ($configs as $config) {
                if ($this->deleteConfigFile($config)) {
                    $report->totalFilesDeleted++;
                }
            }
        }

        return $deleted;
    }

    private function deleteConfigFile(Config $config): bool
    {
        $location = $config->config_location;

        if (empty($location) || str_contains($location, 'tests/storage/')) {
            return false;
        }

        $stillReferenced = Config::query()
            ->where('device_id', $config->device_id)
            ->where('command', $config->command)
            ->where('config_location', $location)
            ->exists();

        if ($stillReferenced || ! File::exists($location)) {
            return false;
        }

        return File::delete($location);
    }

    /**
     * Bulk deletes skip the Config model events that maintain config_summaries, so recount the devices touched.
     */
    private function resyncConfigSummaries(): void
    {
        foreach (array_keys($this->affectedDeviceIds) as $deviceId) {
            $counts = Config::query()
                ->toBase()
                ->where('device_id', $deviceId)
                ->selectRaw('download_status, COUNT(*) as total')
                ->groupBy('download_status')
                ->pluck('total', 'download_status');

            $summary = ConfigSummary::firstOrCreate(['device_id' => $deviceId]);
            $summary->download_status_0_count = (int) ($counts[0] ?? 0);
            $summary->download_status_1_count = (int) ($counts[1] ?? 0);
            $summary->download_status_2_count = (int) ($counts[2] ?? 0);
            $summary->total_count = $summary->download_status_0_count + $summary->download_status_1_count + $summary->download_status_2_count;
            $summary->total_file_count = (int) $counts->sum();
            $summary->save();
        }
    }
}
