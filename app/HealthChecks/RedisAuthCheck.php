<?php

namespace App\HealthChecks;

use App\Services\Utilities\RedisSecurityService;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Surfaces the Redis authentication state on the dashboard health panel. The
 * only healthy state is "a password is required": an unauthenticated Redis lets
 * any local process write queue jobs that a root Horizon worker deserialises.
 *
 * Bind and protected-mode are reported as context in the meta only. They never
 * flip the result to ok, because the loopback bind is not a boundary while the
 * device Telnet path can open sockets to 127.0.0.1.
 */
class RedisAuthCheck extends Check
{
    public function __construct(private readonly RedisSecurityService $service)
    {
        parent::__construct();
    }

    public function run(): Result
    {
        $status = $this->service->status();

        $result = Result::make()
            ->meta([
                'password_required' => $status['password_required'],
                'bind' => $status['bind'],
                'protected_mode' => $status['protected_mode'],
            ]);

        if (! $status['reachable']) {
            return $result
                ->warning('Redis is unreachable; authentication state could not be determined.')
                ->shortSummary('unreachable');
        }

        if ($status['password_required'] === true) {
            return $result
                ->ok('Redis requires authentication.')
                ->shortSummary('authenticated');
        }

        return $result
            ->failed('Redis has no password set. Run: php artisan rconfig:redis-secure')
            ->shortSummary('unauthenticated');
    }
}
