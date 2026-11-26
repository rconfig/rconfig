<?php

namespace App\Jobs;

use App\Models\SettingsPurge;
use App\Traits\LogDataPurge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Deletes rows older than a cutoff in batches. It works to a time budget well inside the Horizon
 * worker timeout and queues itself again while rows remain.
 */
class PurgeTableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogDataPurge, Queueable, SerializesModels;

    public int $timeout = 50;
    public int $tries = 1;

    public function __construct(
        private string $table,
        private int|string $cutoff,
        private string $timestampCol,
        private int $batchSize = 1000,
        private int $timeBudgetSeconds = 30,
        private int $maxBatchesPerJob = 500,
    ) {
        $this->onQueue('PurgeDataJobs');
    }

    public function handle(): void
    {
        if (! in_array($this->table, SettingsPurge::GENERIC_TARGETS, true)) {
            $this->logError("Table {$this->table} is not a supported purge target");

            return;
        }

        if (! DB::getSchemaBuilder()->hasTable($this->table)) {
            $this->logError("Table {$this->table} does not exist");

            return;
        }

        if (! DB::getSchemaBuilder()->hasColumn($this->table, $this->timestampCol)) {
            $this->logError("Column {$this->timestampCol} does not exist in {$this->table}");

            return;
        }

        $startTime = microtime(true);
        $deletedTotal = 0;
        $batchCount = 0;
        $moreToDelete = false;

        do {
            $deleted = DB::table($this->table)
                ->where($this->timestampCol, '<', $this->cutoff)
                ->limit($this->batchSize)
                ->delete();

            $deletedTotal += $deleted;
            $batchCount++;

            $outOfBudget = $batchCount >= $this->maxBatchesPerJob || (microtime(true) - $startTime) >= $this->timeBudgetSeconds;

            if ($deleted > 0 && $outOfBudget) {
                $moreToDelete = true;

                break;
            }
        } while ($deleted > 0);

        if ($moreToDelete) {
            self::dispatch($this->table, $this->cutoff, $this->timestampCol, $this->batchSize, $this->timeBudgetSeconds, $this->maxBatchesPerJob);
        }

        $duration = round(microtime(true) - $startTime, 2);
        $this->logInfo("Purge of {$this->table} deleted {$deletedTotal} records in {$batchCount} batches ({$duration}s)" . ($moreToDelete ? ', continuing in a follow-up job' : ''));
    }

    public function failed(\Throwable $exception): void
    {
        $this->logError("Purge job failed for {$this->table} - Error: {$exception->getMessage()}");
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return ['purge', "table:{$this->table}"];
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getCutoff(): int|string
    {
        return $this->cutoff;
    }

    public function getTimestampCol(): string
    {
        return $this->timestampCol;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }
}
