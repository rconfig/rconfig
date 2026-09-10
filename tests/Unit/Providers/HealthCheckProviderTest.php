<?php

use App\Providers\HealthCheckProvider;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Facades\Health;

test('it registers ping check when enabled and online', function () {
    config()->set('health.ping.enabled', true);
    config()->set('health.ping.offline_mode', false);
    config()->set('health.ping.air_gapped', false);
    config()->set('health.ping.target', 'https://www.rconfig.com');

    Health::shouldReceive('checks')
        ->once()
        ->withArgs(function (array $checks) {
            return containsCheck($checks, PingCheck::class);
        });

    (new HealthCheckProvider($this->app))->boot();
});

test('it skips ping check when ping is disabled', function () {
    config()->set('health.ping.enabled', false);
    config()->set('health.ping.offline_mode', false);
    config()->set('health.ping.air_gapped', false);
    config()->set('health.ping.target', 'https://www.rconfig.com');

    Health::shouldReceive('checks')
        ->once()
        ->withArgs(function (array $checks) {
            return ! containsCheck($checks, PingCheck::class);
        });

    (new HealthCheckProvider($this->app))->boot();
});

test('it skips ping check in offline mode', function () {
    config()->set('health.ping.enabled', true);
    config()->set('health.ping.offline_mode', true);
    config()->set('health.ping.air_gapped', false);
    config()->set('health.ping.target', 'https://www.rconfig.com');

    Health::shouldReceive('checks')
        ->once()
        ->withArgs(function (array $checks) {
            return ! containsCheck($checks, PingCheck::class);
        });

    (new HealthCheckProvider($this->app))->boot();
});

test('it skips ping check in air gapped mode', function () {
    config()->set('health.ping.enabled', true);
    config()->set('health.ping.offline_mode', false);
    config()->set('health.ping.air_gapped', true);
    config()->set('health.ping.target', 'https://www.rconfig.com');

    Health::shouldReceive('checks')
        ->once()
        ->withArgs(function (array $checks) {
            return ! containsCheck($checks, PingCheck::class);
        });

    (new HealthCheckProvider($this->app))->boot();
});

test('it registers ping check for internal target', function () {
    config()->set('health.ping.enabled', true);
    config()->set('health.ping.offline_mode', false);
    config()->set('health.ping.air_gapped', false);
    config()->set('health.ping.target', 'http://127.0.0.1');

    Health::shouldReceive('checks')
        ->once()
        ->withArgs(function (array $checks) {
            return containsCheck($checks, PingCheck::class);
        });

    (new HealthCheckProvider($this->app))->boot();
});

function containsCheck(array $checks, string $className): bool
{
    foreach ($checks as $check) {
        if ($check instanceof $className) {
            return true;
        }
    }

    return false;
}
