<?php

use App\Rules\DeviceIpIsValid;
use Illuminate\Support\Facades\Validator;

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
