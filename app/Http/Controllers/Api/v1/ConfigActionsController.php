<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\ConfigActionsController as BaseConfigActionsController;
use App\Jobs\DownloadConfigNowJob;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

/**
 * @group Configuration Actions
 *
 * @authenticated
 */
class ConfigActionsController extends BaseConfigActionsController
{
    /**
     * Trigger an on-demand configuration download for a single device by id.
     */
    public function apiDownloadNow(int $id): JsonResponse
    {
        if (! Device::whereKey($id)->exists()) {
            return $this->failureResponse('Device not found.', 404);
        }

        if (app()->environment('testing')) {
            dispatch(new DownloadConfigNowJob($id))->onConnection('sync');
        } else {
            dispatch(new DownloadConfigNowJob($id))->onQueue('ManualDownloadQueue');
        }

        return $this->successResponse('Download started for device ' . $id);
    }
}
