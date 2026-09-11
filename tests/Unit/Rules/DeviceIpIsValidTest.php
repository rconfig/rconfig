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
