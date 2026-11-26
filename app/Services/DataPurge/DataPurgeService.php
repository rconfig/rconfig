<?php

namespace App\Services\DataPurge;

use App\Jobs\PurgeTableJob;
use App\Models\SettingsPurge;
use App\Traits\LogDataPurge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Runs the purge described by the settings_purge rows: config retention for the configs target,
 * an age-based delete for every other allowed table.
 *
 * @phpstan-type TableResult array{table: string, status: string, records_found: int, records_deleted: int, queued: bool, message: string|null}
 * @phpstan-type PurgeResults array{total_checked: int, total_queued: int, total_deleted_directly: int, tables_processed: list<TableResult>, errors: list<string>}
 */
class DataPurgeService
{
    use LogDataPurge;

    public const QUEUE = 'PurgeDataJobs';

    private const TIMESTAMP_COLUMNS = ['created_at', 'updated_at', 'failed_at'];

    private const INTEGER_COLUMN_TYPES = ['int', 'integer', 'bigint', 'smallint', 'mediumint', 'tinyint'];

    private bool $dryRun = false;
    private int $queueThreshold = 1000;
    private bool $forceQueue = false;
    private bool $noQueue = false;

    /** @var PurgeResults */
    private array $results = [
        'total_checked' => 0,
        'total_queued' => 0,
        'total_deleted_directly' => 0,
        'tables_processed' => [],
        'errors' => [],
    ];

    public function __construct(private ConfigRetentionService $configRetentionService) {}

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setQueueThreshold(int $threshold): void
    {
        $this->queueThreshold = $threshold;
    }

    public function getQueueThreshold(): int
    {
        return $this->queueThreshold;
    }

    public function setForceQueue(bool $forceQueue): void
    {
        $this->forceQueue = $forceQueue;
    }

    public function setNoQueue(bool $noQueue): void
    {
        $this->noQueue = $noQueue;
    }

    /**
     * @return PurgeResults
     */
    public function execute(?string $specificTable = null): array
    {
        $this->results = [
            'total_checked' => 0,
            'total_queued' => 0,
            'total_deleted_directly' => 0,
            'tables_processed' => [],
            'errors' => [],
        ];

        $settings = SettingsPurge::query()
            ->when($specificTable, fn ($query) => $query->where('target', $specificTable))
            ->orderBy('target')
            ->get();

        foreach ($settings as $setting) {
            $this->results['total_checked']++;

            if ($setting->target === SettingsPurge::TARGET_CONFIGS) {
                $this->handleConfigsRetention($setting);

                continue;
            }

            $this->processPurgeSetting($setting);
        }

        return $this->results;
    }

    private function handleConfigsRetention(SettingsPurge $setting): void
    {
        $skipReason = $this->skipReason($setting);

        if ($skipReason !== null) {
            $this->results['tables_processed'][] = $this->tableResult($setting->target, 'skipped', $skipReason);

            return;
        }

        try {
            $report = $this->configRetentionService->runFromSettings($setting, $this->dryRun);
        } catch (\Throwable $e) {
            $this->recordError($setting->target, 'Error purging configs: ' . $e->getMessage(), $e->getMessage());

            return;
        }

        $found = $report->totalRecordsToDelete();
        $deleted = $report->totalRecordsDeleted();

        if ($this->dryRun) {
            $this->results['tables_processed'][] = $this->tableResult(
                $setting->target,
                $found > 0 ? 'dry_run' : 'success',
                $found > 0 ? "Would delete {$found} config-related records (changes and configs)" : 'No config-related records to purge',
                $found,
            );

            return;
        }

        $this->results['total_deleted_directly'] += $deleted;
        $this->results['tables_processed'][] = $this->tableResult(
            $setting->target,
            'success',
            $deleted > 0 ? "Deleted {$deleted} config-related records (changes and configs)" : 'No config-related records to purge',
            $found,
            $deleted,
        );

        if ($deleted > 0) {
            $this->logInfo("Deleted {$deleted} config-related records (changes and configs), {$report->totalFilesDeleted} files removed");
        }
    }

    private function processPurgeSetting(SettingsPurge $setting): void
    {
        $table = $setting->target;

        try {
            $skipReason = $this->skipReason($setting);

            if ($skipReason !== null) {
                $this->results['tables_processed'][] = $this->tableResult($table, 'skipped', $skipReason);

                return;
            }

            if (! in_array($table, SettingsPurge::GENERIC_TARGETS, true)) {
                $this->recordError($table, "Table {$table} is not a supported purge target", 'Not a supported purge target');

                return;
            }

            if (! DB::getSchemaBuilder()->hasTable($table)) {
                $this->recordError($table, "Table {$table} does not exist", 'Table does not exist');

                return;
            }

            $timestamp = $this->findTimestampColumn($table);

            if ($timestamp === null) {
                $this->recordError($table, "No timestamp column found for {$table}", 'No timestamp column found');

                return;
            }

            $cutoff = $this->getCutoffValue((int) $setting->retention_days, $timestamp['is_integer']);
            $recordsToDelete = DB::table($table)->where($timestamp['column'], '<', $cutoff)->count();

            if ($recordsToDelete === 0) {
                $this->results['tables_processed'][] = $this->tableResult($table, 'success', 'No records to purge');

                return;
            }

            if ($this->dryRun) {
                $this->results['tables_processed'][] = $this->tableResult($table, 'dry_run', "Would delete {$recordsToDelete} records", $recordsToDelete);

                return;
            }

            if ($this->shouldUseQueue($recordsToDelete)) {
                PurgeTableJob::dispatch($table, $cutoff, $timestamp['column'], $setting->getBatchSize());

                $this->results['total_queued']++;
                $this->results['tables_processed'][] = $this->tableResult($table, 'queued', "Queued {$recordsToDelete} records for deletion", $recordsToDelete, 0, true);
                $this->logInfo("Queued {$recordsToDelete} records from {$table} for deletion");

                return;
            }

            $deleted = $this->deleteRecordsDirectly($table, $timestamp['column'], $cutoff, $setting->getBatchSize());

            $this->results['total_deleted_directly'] += $deleted;
            $this->results['tables_processed'][] = $this->tableResult($table, 'success', "Deleted {$deleted} records directly", $recordsToDelete, $deleted);
            $this->logInfo("Deleted {$deleted} records from {$table}");
        } catch (\Throwable $e) {
            $this->recordError($table, "Error purging {$table}: " . $e->getMessage(), $e->getMessage());
        }
    }

    /**
     * Why this setting is not purged on this run, or null when it should run.
     */
    private function skipReason(SettingsPurge $setting): ?string
    {
        if ((int) $setting->retention_days === 0) {
            return 'Retention days is 0';
        }

        if (! $setting->isEnabled()) {
            return 'Purge is disabled';
        }

        if (! $this->shouldRunBasedOnFrequency($setting->getCleanupFrequency())) {
            return 'Not scheduled to run today';
        }

        return null;
    }

    /**
     * Weekly targets run on Sunday, monthly targets on the 1st.
     */
    private function shouldRunBasedOnFrequency(string $frequency): bool
    {
        $today = Carbon::today();

        return match ($frequency) {
            'weekly' => $today->isSunday(),
            'monthly' => $today->day === 1,
            default => true,
        };
    }

    /**
     * @return array{column: string, is_integer: bool}|null
     */
    private function findTimestampColumn(string $table): ?array
    {
        foreach (self::TIMESTAMP_COLUMNS as $column) {
            if (DB::getSchemaBuilder()->hasColumn($table, $column)) {
                return [
                    'column' => $column,
                    'is_integer' => in_array(strtolower(DB::getSchemaBuilder()->getColumnType($table, $column)), self::INTEGER_COLUMN_TYPES, true),
                ];
            }
        }

        return null;
    }

    /**
     * jobs and job_batches store created_at as a unix timestamp, every other target as a datetime.
     */
    private function getCutoffValue(int $retentionDays, bool $isInteger): int|string
    {
        $cutoff = Carbon::now()->subDays($retentionDays);

        return $isInteger ? $cutoff->timestamp : $cutoff->toDateTimeString();
    }

    private function shouldUseQueue(int $recordCount): bool
    {
        if ($this->forceQueue) {
            return true;
        }

        if ($this->noQueue) {
            return false;
        }

        return $recordCount >= $this->queueThreshold;
    }

    private function deleteRecordsDirectly(string $table, string $column, int|string $cutoff, int $batchSize): int
    {
        $totalDeleted = 0;

        do {
            $deleted = DB::table($table)->where($column, '<', $cutoff)->limit($batchSize)->delete();
            $totalDeleted += $deleted;
        } while ($deleted > 0);

        return $totalDeleted;
    }

    private function recordError(string $table, string $error, string $message): void
    {
        $this->results['tables_processed'][] = $this->tableResult($table, 'error', $message);
        $this->results['errors'][] = $error;
        $this->logError($error);
    }

    /**
     * @return TableResult
     */
    private function tableResult(string $table, string $status, ?string $message, int $found = 0, int $deleted = 0, bool $queued = false): array
    {
        return [
            'table' => $table,
            'status' => $status,
            'records_found' => $found,
            'records_deleted' => $deleted,
            'queued' => $queued,
            'message' => $message,
        ];
    }
}
