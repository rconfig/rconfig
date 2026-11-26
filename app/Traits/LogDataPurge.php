<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

/**
 * Logging for data purge code. Writes to the application log and the activity log, without the
 * per-user notification activityLogIt() sends, because a purge run reports once through the
 * notification preferences system instead.
 */
trait LogDataPurge
{
    private function logInfo(string $message): void
    {
        Log::info("[DataPurge] {$message}");
        $this->writePurgeActivity('info', $message, __FUNCTION__);
    }

    private function logError(string $message): void
    {
        Log::error("[DataPurge] {$message}");
        $this->writePurgeActivity('error', $message, __FUNCTION__);
    }

    private function logWarning(string $message): void
    {
        Log::warning("[DataPurge] {$message}");
        $this->writePurgeActivity('warn', $message, __FUNCTION__);
    }

    private function writePurgeActivity(string $level, string $message, string $function): void
    {
        try {
            activity($level)
                ->tap(function (Activity $activity) use ($function): void {
                    $activity->event_type = 'data_purge';
                    $activity->class = static::class;
                    $activity->function = $function;
                })
                ->log($message);
        } catch (\Throwable $e) {
            Log::warning('[DataPurge] Could not write activity log entry: ' . $e->getMessage());
        }
    }
}
