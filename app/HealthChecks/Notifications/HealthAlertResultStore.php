<?php

namespace App\HealthChecks\Notifications;

use App\Services\Notifications\HealthCheckNotifier;
use App\Services\Notifications\Shared\HealthCheckAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\EloquentHealthResultStore;
use Spatie\Health\ResultStores\ResultStore;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResults;

/**
 * Raises health-check alerts through rConfig's notification channels instead of
 * spatie's own mail notification (which stays disabled in config/health.php).
 *
 * Registered first in health.result_stores, so when save() runs the history table
 * still holds each check's previous result. Only transitions notify: a check that
 * starts failing, and a failing check that passes again. Warnings never notify.
 * Nothing here may throw, or the Eloquent store after it would not save the run.
 */
class HealthAlertResultStore implements ResultStore
{
    /**
     * Statuses that count as failing. Crashed means the check itself threw.
     */
    private const FAILING_STATUSES = ['failed', 'crashed'];

    public function __construct(private readonly HealthCheckNotifier $notifier) {}

    /** @param  Collection<int, Result>  $checkResults */
    public function save(Collection $checkResults): void
    {
        try {
            $previousStatuses = $this->previousStatuses($checkResults);

            $newlyFailing = [];
            $recovered = [];

            foreach ($checkResults as $result) {
                $current = (string) $result->status->value;

                if ($current === Status::skipped()->value) {
                    continue;
                }

                $wasFailing = in_array($previousStatuses[$result->check->getName()] ?? null, self::FAILING_STATUSES, true);
                $isFailing = in_array($current, self::FAILING_STATUSES, true);

                if ($isFailing && ! $wasFailing) {
                    $newlyFailing[] = $result;
                } elseif ($wasFailing && ! $isFailing) {
                    $recovered[] = $result;
                }
            }

            if ($newlyFailing !== []) {
                $this->notifier->notify(HealthCheckAlert::fromResults(false, $newlyFailing));
            }

            if ($recovered !== []) {
                $this->notifier->notify(HealthCheckAlert::fromResults(true, $recovered));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to raise health check notifications: ' . $e->getMessage());
        }
    }

    /**
     * Spatie binds ResultStore to the first configured store (health:list, the JSON
     * results endpoint), so reads are handed to the Eloquent store that holds them.
     */
    public function latestResults(): ?StoredCheckResults
    {
        return app(EloquentHealthResultStore::class)->latestResults();
    }

    /**
     * Latest stored, non-skipped status per check in this run. Checks that only run
     * occasionally (e.g. hourly) are compared with their last real run, not the last batch.
     *
     * @param  Collection<int, Result>  $checkResults
     * @return array<string, string>
     */
    private function previousStatuses(Collection $checkResults): array
    {
        $model = EloquentHealthResultStore::determineHistoryItemModel();
        $checkNames = $checkResults->map(fn (Result $result): string => $result->check->getName())->unique()->values()->all();

        $latestIds = $model::query()
            ->selectRaw('MAX(id)')
            ->whereIn('check_name', $checkNames)
            ->where('status', '!=', Status::skipped()->value)
            ->groupBy('check_name');

        return $model::query()
            ->whereIn('id', $latestIds)
            ->pluck('status', 'check_name')
            ->map(fn ($status): string => (string) $status)
            ->all();
    }
}
