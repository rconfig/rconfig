<?php

use App\HealthChecks\RedisAuthCheck;
use App\Services\Utilities\RedisSecurityService;
use Spatie\Health\Enums\Status;

function checkWith(array $status): RedisAuthCheck
{
    $service = Mockery::mock(RedisSecurityService::class);
    $service->shouldReceive('status')->once()->andReturn($status);

    return new RedisAuthCheck($service);
}

function baseStatus(array $overrides = []): array
{
    return array_merge([
        'reachable' => true,
        'password_required' => true,
        'app_authenticated' => true,
        'secure' => true,
        'bind' => '127.0.0.1',
        'protected_mode' => 'yes',
        'detail' => '',
    ], $overrides);
}

test('it is ok only when a password is required', function () {
    $result = checkWith(baseStatus())->run();

    expect($result->status->equals(Status::ok()))->toBeTrue();
    expect($result->getShortSummary())->toBe('authenticated');
});

test('it fails when Redis has no password and points at the fix command', function () {
    $result = checkWith(baseStatus([
        'password_required' => false,
        'secure' => false,
    ]))->run();

    expect($result->status->equals(Status::failed()))->toBeTrue();
    expect($result->getShortSummary())->toBe('unauthenticated');
    expect($result->getNotificationMessage())->toContain('rconfig:redis-secure');
});

test('it warns rather than passing when Redis is unreachable', function () {
    $result = checkWith(baseStatus([
        'reachable' => false,
        'password_required' => null,
        'app_authenticated' => false,
        'secure' => false,
    ]))->run();

    expect($result->status->equals(Status::warning()))->toBeTrue();
    expect($result->getShortSummary())->toBe('unreachable');
});

test('bind and protected-mode are carried as context meta only', function () {
    $result = checkWith(baseStatus([
        'password_required' => false,
        'secure' => false,
        'bind' => '0.0.0.0',
        'protected_mode' => 'no',
    ]))->run();

    // Context must never turn a passwordless instance green.
    expect($result->status->equals(Status::failed()))->toBeTrue();
    expect($result->meta['bind'])->toBe('0.0.0.0');
    expect($result->meta['protected_mode'])->toBe('no');
});
