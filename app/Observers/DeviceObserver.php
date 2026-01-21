<?php

namespace App\Observers;

use App\Models\Config;
use App\Models\Device;

class DeviceObserver
{
    public function deleting(Device $device): void
    {
        // Clean up configs and their change records tied to the device
        $configIds = Config::query()
            ->where('device_id', $device->id)
            ->pluck('id')
            ->all();

        if (empty($configIds)) {
            return;
        }

        // Delete configs in chunks to avoid memory issues
        foreach (array_chunk($configIds, 200) as $chunk) {
            Config::query()
                ->whereIn('id', $chunk)
                ->get()
                ->each->delete(); // per-model delete to trigger Config model events (file cleanup, summary updates)
        }
    }
}