<?php

use App\Rules\DeviceIpIsValid;
use App\Services\Utilities\HostResolver;
use Illuminate\Support\Facades\Validator;
use Tests\Traits\FakesHostResolver;

uses(FakesHostResolver::class);

/*
 * DNS is faked for every test here, so a name only resolves when it is in this map.
 * Names that point at loopback are the GHSA-p5q7-2wwp-q42f bypass of RCO-1813.
 */
beforeEach(function () {
    $this->fakeHostResolver([
        'example.com' => ['93.184.215.14', '2606:2800:21f:cb07:6820:80da:af6b:8b2c'],
        'core-sw01' => ['10.20.30.40'],
        '127-router.example.com' => ['192.0.2.10'],
        '127.0.0.1.nip.io' => ['127.0.0.1'],
        'localtest.me' => ['127.0.0.1'],
        'loopback6.example.com' => ['::1'],
        'mapped.example.com' => ['::ffff:127.0.0.1'],
        'dual.example.com' => ['192.0.2.20', '::1'],
        'zero.example.com' => ['0.0.0.0'],
        'redis' => ['172.18.0.3'],
        'redis-alias.example.com' => ['172.18.0.3'],
    ]);
});

function makeValidator(array $data): Illuminate\Contracts\Validation\Validator
{
    $rule = ['ip_address' => [new DeviceIpIsValid]];

    return Validator::make($data, $rule);
}

test('it allows valid ipv4 address', function () {
    $validator = makeValidator(['ip_address' => '192.168.1.1']);
    expect($validator->passes())->toBeTrue();
});

test('it allows valid ipv6 address', function () {
    $validator = makeValidator(['ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);
    expect($validator->passes())->toBeTrue();
});

test('it allows valid fqdn', function () {
    $validator = makeValidator(['ip_address' => 'example.com']);
    expect($validator->passes())->toBeTrue();
});

test('it fails for invalid fqdn', function () {
    $validator = makeValidator(['ip_address' => 'invalid_domain..com']);
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->first('ip_address'))->toEqual('The ip address must be a valid IP address or FQDN/Hostname.');
});

test('it fails for null value', function () {
    $validator = makeValidator(['ip_address' => null]);
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->first('ip_address'))->toEqual('The ip address is required.');
});

test('it allows ipv4 mapped ipv6 address', function () {
    $validator = makeValidator(['ip_address' => '::ffff:192.0.2.128']);
    expect($validator->passes())->toBeTrue();
});

test('it allows mixed case fqdn', function () {
    $validator = makeValidator(['ip_address' => 'ExAmPle.CoM']);
    expect($validator->passes())->toBeTrue();
});

/*
 * A device on loopback let any user make rConfig write into its own Redis. CORE has no
 * RBAC, so every user can add a device. See RCO-1813.
 */
test('it rejects targets pointing at this server', function (string $target) {
    $validator = makeValidator(['ip_address' => $target]);

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->first('ip_address'))->toEqual('The ip address cannot point at this rConfig server (loopback, localhost or its Redis host).');
})->with([
    'ipv4 loopback' => '127.0.0.1',
    'ipv4 loopback range' => '127.5.5.5',
    'ipv6 loopback' => '::1',
    'ipv4 mapped loopback' => '::ffff:127.0.0.1',
    'unspecified ipv4' => '0.0.0.0',
    'unspecified ipv6' => '::',
    'localhost' => 'localhost',
    'localhost uppercase' => 'LOCALHOST',
    'localhost subdomain' => 'redis.localhost',
]);

test('it rejects the configured redis host', function () {
    config(['database.redis.default.host' => 'redis']);

    expect(makeValidator(['ip_address' => 'redis'])->passes())->toBeFalse();
    expect(makeValidator(['ip_address' => 'REDIS'])->passes())->toBeFalse();
});

/*
 * GHSA-p5q7-2wwp-q42f: the RCO-1813 guard only matched literal IPs and never resolved a
 * name, while the connectors do. Every one of these passed the old rule.
 */
test('it rejects names that resolve to this server', function (string $target) {
    $validator = makeValidator(['ip_address' => $target]);

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->first('ip_address'))->toEqual('The ip address cannot point at this rConfig server (loopback, localhost or its Redis host).');
})->with([
    'wildcard dns to loopback' => '127.0.0.1.nip.io',
    'public name with a loopback A record' => 'localtest.me',
    'uppercase and trailing dot' => 'LOCALTEST.ME.',
    'AAAA record to ::1' => 'loopback6.example.com',
    'AAAA record to ipv4 mapped loopback' => 'mapped.example.com',
    'one loopback address among several' => 'dual.example.com',
    'A record to 0.0.0.0' => 'zero.example.com',
]);

test('it rejects any name that resolves to the redis host address', function () {
    config(['database.redis.default.host' => 'redis']);

    expect(makeValidator(['ip_address' => 'redis-alias.example.com'])->passes())->toBeFalse();
    expect(makeValidator(['ip_address' => '172.18.0.3'])->passes())->toBeFalse();
});

/*
 * These resolve through the real resolver with no DNS query: the C library reads them as
 * shorthand IPv4 (inet_aton), which is how fsockopen dials them.
 */
test('it rejects shorthand ipv4 forms of loopback', function (string $target) {
    app()->forgetInstance(HostResolver::class);

    expect(makeValidator(['ip_address' => $target])->passes())->toBeFalse();
})->with([
    'decimal' => '2130706433',
    'two part' => '127.1',
    'octal' => '0177.0.0.1',
]);

test('it rejects a public wildcard dns name for loopback', function () {
    app()->forgetInstance(HostResolver::class);

    expect(makeValidator(['ip_address' => '127.0.0.1.nip.io'])->passes())->toBeFalse();
})->group('external-network');

test('it allows a name that does not resolve, the connector refuses it at dial time', function () {
    expect(makeValidator(['ip_address' => 'not-in-dns-yet.example.com'])->passes())->toBeTrue();
});

test('it still allows normal device addresses', function (string $target) {
    expect(makeValidator(['ip_address' => $target])->passes())->toBeTrue();
})->with([
    'rfc1918 10/8' => '10.0.0.1',
    'rfc1918 172.16/12' => '172.16.5.5',
    'rfc1918 192.168/16' => '192.168.1.1',
    'public ipv4' => '8.8.8.8',
    'ipv6' => '2001:db8::1',
    'hostname' => 'core-sw01',
    'hostname starting with 127' => '127-router.example.com',
]);
