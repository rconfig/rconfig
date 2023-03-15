<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\RespondsWithHttpStatus;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;

class SystemHealthController extends Controller
{
    use RespondsWithHttpStatus;

    public function healthLatest()
    {

        if (!$latestItem = (HealthCheckResultHistoryItem::latest()->first())) {
            return null;
        }

        /** @var Collection<int, StoredCheckResult> $storedCheckResults */
        $storedCheckResults = (HealthCheckResultHistoryItem::query()
            ->where('batch', $latestItem->batch)
            ->get()
            ->map(function (HealthCheckResultHistoryItem $item) {
                return new StoredCheckResult(
                    $item->check_name,
                    $item->check_label,
                    $item->status,
                    $item->notification_message,
                    $item->short_summary === 'reachable' ? 'rconfig.com reachable' : $item->short_summary,
                    $item->meta,
                    $item->ended_at
                );
            })
        );

        return $this->successResponse('Success', $storedCheckResults);
    }
}
