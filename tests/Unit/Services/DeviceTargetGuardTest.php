<?php

use App\Exceptions\DeviceTargetRefusedException;
use App\Services\Device\DeviceTargetGuard;
use Tests\Traits\FakesHostResolver;

uses(FakesHostResolver::class);

/*
 * DeviceTargetGuard::dialHost() is what the connectors hand to fsockopen and phpseclib,
 * so the address it checks is the address dialled (GHSA-p5q7-2wwp-q42f).
 */
beforeEach(function () {
    config(['database.redis.default.host' => '127.0.0.1']);

    $this->fakeHostResolver([
        'router.example.com' => ['192.0.2.10'],
        'dual.example.com' => ['2001:db8::10', '192.0.2.20'],
        'v6only.example.com' => ['2001:db8::30'],
        'sneaky.example.com' => ['192.0.2.40', '127.0.0.1'],
    ]);
});

function deviceTargetGuard(): DeviceTargetGuard
{
    return app(DeviceTargetGuard::class);
}

test('an ipv4 literal is dialled as written', function () {
    expect(deviceTargetGuard()->dialHost('10.1.1.1'))->toBe('10.1.1.1');
});

test('an ipv6 literal is bracketed for the socket layer', function () {
    expect(deviceTargetGuard()->dialHost('2001:db8::1'))->toBe('[2001:db8::1]');
    expect(deviceTargetGuard()->dialHost('[2001:db8::1]'))->toBe('[2001:db8::1]');
});

test('a name is dialled by its resolved address', function () {
    expect(deviceTargetGuard()->dialHost('router.example.com'))->toBe('192.0.2.10');
    expect(deviceTargetGuard()->dialHost('Router.Example.Com.'))->toBe('192.0.2.10');
});

test('a dual stack name prefers ipv4', function () {
    expect(deviceTargetGuard()->dialHost('dual.example.com'))->toBe('192.0.2.20');
});

test('an ipv6 only name is dialled bracketed', function () {
    expect(deviceTargetGuard()->dialHost('v6only.example.com'))->toBe('[2001:db8::30]');
});

test('a name with any loopback address is refused, even alongside a real one', function () {
    deviceTargetGuard()->dialHost('sneaky.example.com');
})->throws(DeviceTargetRefusedException::class, 'points at this rConfig server');

test('loopback, unspecified and localhost targets are refused', function (string $target) {
    deviceTargetGuard()->dialHost($target);
})->throws(DeviceTargetRefusedException::class, 'points at this rConfig server')->with([
    '127.0.0.1', '127.9.9.9', '0.0.0.0', '::1', '[::1]', '::', '::ffff:127.0.0.1', 'localhost', 'db.localhost',
]);

test('a name that does not resolve fails closed', function () {
    deviceTargetGuard()->dialHost('nowhere.example.com');
})->throws(DeviceTargetRefusedException::class, 'could not be resolved');

test('the redis host is refused by any name or literal for it', function () {
    config(['database.redis.default.host' => 'redis']);
    $this->fakeHostResolver(['redis' => ['172.18.0.3'], 'cache.example.com' => ['172.18.0.3']]);

    expect(deviceTargetGuard()->pointsAtThisServer('redis'))->toBeTrue();
    expect(deviceTargetGuard()->pointsAtThisServer('cache.example.com'))->toBeTrue();
    expect(deviceTargetGuard()->pointsAtThisServer('172.18.0.3'))->toBeTrue();
    expect(deviceTargetGuard()->pointsAtThisServer('172.18.0.4'))->toBeFalse();
});
