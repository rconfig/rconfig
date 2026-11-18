<?php

namespace Tests\Traits;

use App\Services\Device\DeviceTargetGuard;
use App\Services\Utilities\HostResolver;

/**
 * For connection tests that stand up a local listener on 127.0.0.1 to simulate a
 * device. The real guard refuses loopback (GHSA-p5q7-2wwp-q42f), so these tests
 * swap in one that dials the target as written. Never use it in a test about the
 * guard itself: LoopbackDeviceTargetRefusedTest covers that.
 */
trait AllowsLoopbackDeviceTargets
{
    protected function allowLoopbackDeviceTargets(): void
    {
        app()->instance(DeviceTargetGuard::class, new class(new HostResolver) extends DeviceTargetGuard
        {
            public function pointsAtThisServer(string $target): bool
            {
                return false;
            }

            public function dialHost(string $target): string
            {
                return filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? "[{$target}]" : $target;
            }
        });
    }
}
