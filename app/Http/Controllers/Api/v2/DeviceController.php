<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\DeviceController as BaseDeviceController;
use App\Models\Config;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

/**
 * @group Devices
 *
 * @authenticated
 */
class DeviceController extends BaseDeviceController
{
    // Inherits index/show/store/update/destroy/enable/disable (plus credential masking)
    // from the application device controller.

    /**
     * High-level backup summary across all devices for dashboards.
     */
    public function summary(): JsonResponse
    {
        $totalDevices = Device::count();

        $latestConfigIds = Config::query()
            ->selectRaw('MAX(id) as latest_id')
            ->groupBy('device_id');

        $latestConfigs = Config::query()->whereIn('id', $latestConfigIds->pluck('latest_id'));

        $backedUpDevices = (clone $latestConfigs)->count();

        return $this->successResponse('Device summary', [
            'total_devices' => $totalDevices,
            'backup_success_last_run' => (clone $latestConfigs)->where('download_status', 1)->count(),
            'backup_failed_last_run' => (clone $latestConfigs)->where('download_status', 0)->count(),
            'never_backed_up' => max(0, $totalDevices - $backedUpDevices),
            'last_run_at' => (clone $latestConfigs)->max('created_at'),
        ]);
    }
}
