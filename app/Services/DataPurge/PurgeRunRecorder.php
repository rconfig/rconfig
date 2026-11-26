<?php

namespace App\Services\DataPurge;

use App\Models\PurgeRun;
use Illuminate\Support\Facades\Log;

class PurgeRunRecorder
{
    private const MAX_ERROR_MESSAGE_LENGTH = 4000;

    private const MAX_SUMMARY_JSON_BYTES = 262144;

    private const MAX_ARRAY_PREVIEW_ITEMS = 200;

    private const MAX_STRING_LENGTH = 2000;

    /**
     * Start a new purge/maintenance run.
     *
     * @param  string  $type  e.g. 'data_purge', 'config_history_audit', 'config_history_repair'
     * @param  array  $scope  Arbitrary scope info (table, device_id, etc.)
     * @param  array  $options  CLI/options flags (dry_run, json, queue, etc.) – stored inside summary->options
     * @param  bool  $dryRun  Explicit dry-run flag
     * @param  string|null  $mode  e.g. 'audit', 'repair', 'purge', 'retention'
     * @param  string|null  $source  e.g. 'rconfig:data-purge', 'rconfig:config-history:repair'
     */
    public function start(
        string $type,
        array $scope = [],
        array $options = [],
        bool $dryRun = false,
        ?string $mode = null,
        ?string $source = null,
    ): PurgeRun {
        $summary = [];

        if (! empty($options)) {
            $summary['options'] = $options;
        }

        return PurgeRun::create([
            'type' => $type,
            'mode' => $mode,
            'dry_run' => $dryRun,
            'status' => 'running',
            'source' => $source,
            'scope' => $scope ?: null,
            'summary' => $summary ?: null,
            'message' => null,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the run as finished (success or with issues) and store metrics in summary->metrics.
     */
    public function finish(PurgeRun $run, ?array $metrics, string $status = 'success'): void
    {
        $summary = $run->summary ?? [];

        if ($metrics !== null) {
            $summary['metrics'] = $this->sanitizeMetrics($metrics);
        }

        $run->update([
            'status' => $status,
            'summary' => $summary ?: null,
            'finished_at' => now(),
        ]);
    }

    /**
     * Mark the run as failed and capture the error.
     */
    public function fail(PurgeRun $run, \Throwable $e): void
    {
        try {
            $run->update([
                'status' => 'failed',
                'message' => $this->sanitizeExceptionMessage($e),
                'finished_at' => now(),
            ]);
        } catch (\Throwable $recorderError) {
            // Never let recorder-side failures mask the original exception.
            // Most common cause: the DB is unreachable (which is also why the run failed).
            Log::error('PurgeRunRecorder::fail() could not persist failure state', [
                'purge_run_id' => $run->id ?? null,
                'recorder_error' => $recorderError->getMessage(),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    private function sanitizeExceptionMessage(\Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($message === '') {
            $message = 'Unknown error';
        }

        if (mb_strlen($message) <= self::MAX_ERROR_MESSAGE_LENGTH) {
            return $message;
        }

        $truncatedCharacters = mb_strlen($message) - self::MAX_ERROR_MESSAGE_LENGTH;

        return mb_substr($message, 0, self::MAX_ERROR_MESSAGE_LENGTH)
            . "... [truncated {$truncatedCharacters} chars]";
    }

    private function sanitizeMetrics(array $metrics): array
    {
        $sanitized = $this->sanitizeValue($metrics);

        if (! is_array($sanitized)) {
            return ['notice' => 'Unable to store metrics payload'];
        }

        $encoded = json_encode($sanitized);
        if ($encoded !== false && strlen($encoded) <= self::MAX_SUMMARY_JSON_BYTES) {
            return $sanitized;
        }

        return [
            'notice' => 'Metrics payload truncated due to size limits',
            'totals' => $this->extractScalarTotals($metrics),
        ];
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            if (mb_strlen($value) <= self::MAX_STRING_LENGTH) {
                return $value;
            }

            $overflow = mb_strlen($value) - self::MAX_STRING_LENGTH;

            return mb_substr($value, 0, self::MAX_STRING_LENGTH) . "... [truncated {$overflow} chars]";
        }

        if (! is_array($value)) {
            return $value;
        }

        $result = [];
        $isSequential = array_keys($value) === range(0, count($value) - 1);

        if ($isSequential) {
            $limited = array_slice($value, 0, self::MAX_ARRAY_PREVIEW_ITEMS);
            foreach ($limited as $item) {
                $result[] = $this->sanitizeValue($item);
            }

            if (count($value) > self::MAX_ARRAY_PREVIEW_ITEMS) {
                $result[] = [
                    '_truncated' => true,
                    'total_items' => count($value),
                ];
            }

            return $result;
        }

        foreach ($value as $key => $item) {
            if (in_array($key, ['configIdsToDelete', 'configChangeIdsToDelete', 'nonDownloadedConfigIdsToDelete'], true) && is_array($item)) {
                $result[$key] = [
                    '_truncated' => true,
                    'total_items' => count($item),
                ];

                continue;
            }

            $result[$key] = $this->sanitizeValue($item);
        }

        return $result;
    }

    private function extractScalarTotals(array $metrics): array
    {
        $totals = [];

        foreach ($metrics as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $totals[$key] = $value;
            }
        }

        return $totals;
    }
}
