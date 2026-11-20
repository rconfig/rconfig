<?php

namespace App\Services\Security\Checks\Redis;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\Utilities\RedisSecurityService;

/**
 * Same Redis state as the Redis Auth health check and rconfig:redis-security-check.
 */
class RedisAuthCheck extends AbstractSecurityCheck
{
    protected string $id = 'redis.auth';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Redis;
    protected string $title = 'Redis requires a password';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'rConfig\'s workers run jobs taken from Redis. Without a password, any process that can reach Redis can write a job that the workers then run.';
    protected array $references = ['NIST IA-2', 'NIST AC-3', 'Redis security guidance (requirepass/ACL)'];

    public function __construct(private readonly RedisSecurityService $redis) {}

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $status = $this->redis->status();
        $evidence = ['reachable' => $status['reachable'], 'password_required' => $status['password_required'], 'app_authenticated' => $status['app_authenticated']];

        if (! $status['reachable']) {
            return SecurityCheckResult::skip('Redis could not be reached, so its authentication could not be checked.', $evidence);
        }

        if ($status['password_required'] !== true) {
            return SecurityCheckResult::fail('Redis accepts commands without a password.', $this->artisan('rconfig:redis-secure'), $evidence);
        }

        if (! $status['app_authenticated']) {
            return SecurityCheckResult::fail('Redis requires a password, but REDIS_PASSWORD in .env does not match it, so queues are failing.', $this->artisan('rconfig:redis-secure --force'), $evidence);
        }

        return SecurityCheckResult::pass('Redis requires a password, and rConfig authenticates with it.', $evidence);
    }

    public function fixGuide(): string
    {
        return $this->artisan('rconfig:redis-secure') . '. If Redis already has a password that REDIS_PASSWORD in .env does not match, add --force.';
    }
}
