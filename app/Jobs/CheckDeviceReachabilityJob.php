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

    protected $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function handle(PingService $pingService)
    {
        $reachable = $pingService->check($this->device->device_ip);

        $this->device->update([
            'status' => $reachable ? 1 : 0,
        ]);
    }
}
