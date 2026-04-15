<?php

namespace Tests\Unit\Providers;

use App\Providers\HealthCheckProvider;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Facades\Health;
use Tests\TestCase;

class HealthCheckProviderTest extends TestCase
{
    public function test_it_registers_ping_check_when_enabled_and_online()
    {
        config()->set('health.ping.enabled', true);
        config()->set('health.ping.offline_mode', false);
        config()->set('health.ping.air_gapped', false);
        config()->set('health.ping.target', 'https://www.rconfig.com');

        Health::shouldReceive('checks')
            ->once()
            ->withArgs(function (array $checks) {
                return $this->containsCheck($checks, PingCheck::class);
            });

        (new HealthCheckProvider($this->app))->boot();
    }

    public function test_it_skips_ping_check_when_ping_is_disabled()
    {
        config()->set('health.ping.enabled', false);
        config()->set('health.ping.offline_mode', false);
        config()->set('health.ping.air_gapped', false);
        config()->set('health.ping.target', 'https://www.rconfig.com');

        Health::shouldReceive('checks')
            ->once()
            ->withArgs(function (array $checks) {
                return ! $this->containsCheck($checks, PingCheck::class);
            });

        (new HealthCheckProvider($this->app))->boot();
    }

    public function test_it_skips_ping_check_in_offline_mode()
    {
        config()->set('health.ping.enabled', true);
        config()->set('health.ping.offline_mode', true);
        config()->set('health.ping.air_gapped', false);
        config()->set('health.ping.target', 'https://www.rconfig.com');

        Health::shouldReceive('checks')
            ->once()
            ->withArgs(function (array $checks) {
                return ! $this->containsCheck($checks, PingCheck::class);
            });

        (new HealthCheckProvider($this->app))->boot();
    }

    public function test_it_skips_ping_check_in_air_gapped_mode()
    {
        config()->set('health.ping.enabled', true);
        config()->set('health.ping.offline_mode', false);
        config()->set('health.ping.air_gapped', true);
        config()->set('health.ping.target', 'https://www.rconfig.com');

        Health::shouldReceive('checks')
            ->once()
            ->withArgs(function (array $checks) {
                return ! $this->containsCheck($checks, PingCheck::class);
            });

        (new HealthCheckProvider($this->app))->boot();
    }

    public function test_it_registers_ping_check_for_internal_target()
    {
        config()->set('health.ping.enabled', true);
        config()->set('health.ping.offline_mode', false);
        config()->set('health.ping.air_gapped', false);
        config()->set('health.ping.target', 'http://127.0.0.1');

        Health::shouldReceive('checks')
            ->once()
            ->withArgs(function (array $checks) {
                return $this->containsCheck($checks, PingCheck::class);
            });

        (new HealthCheckProvider($this->app))->boot();
    }

    private function containsCheck(array $checks, string $className): bool
    {
        foreach ($checks as $check) {
            if ($check instanceof $className) {
                return true;
            }
        }

        return false;
    }
}
