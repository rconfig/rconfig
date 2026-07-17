<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\Device\PingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckDeviceReachabilityJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(protected int $device_id) {}

    public function handle(PingService $pingService)
    {
        $device = Device::findOrFail($this->device_id);

        $reachable = $pingService->check($device->device_ip);

        $device->update([
            'status' => $reachable ? 1 : 0,
        ]);
    }
}
