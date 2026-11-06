<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use phpseclib4\Net\SSH2;
use Tests\Unit\Connections\DeviceParamsBuilder;

function resolveSshTimeout(mixed $timeout): int
{
    $reflection = new ReflectionClass(SshConnect::class);
    $connect = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('timeout')->setValue($connect, $timeout);

    return $reflection->getMethod('sshTimeoutValidOrNone')->invoke($connect);
}

test('ssh keeps a valid timeout', function () {
    expect(resolveSshTimeout(30))->toBe(30);
});

test('ssh keeps a valid timeout given as a string', function () {
    expect(resolveSshTimeout('45'))->toBe(45);
});

test('ssh rounds a fractional timeout up so it never becomes no timeout', function () {
    expect(resolveSshTimeout(0.5))->toBe(1);
    expect(resolveSshTimeout('2.5'))->toBe(3);
});

test('ssh maps a missing or unusable timeout to no timeout, as phpseclib 3 did', function (mixed $timeout) {
    expect(resolveSshTimeout($timeout))->toBe(0);
})->with([
    'null' => [null],
    'empty string' => [''],
    'not a number' => ['abc'],
    'negative' => [-5],
]);

test('ssh connect builds a phpseclib 4 client when the template timeout is blank', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['connect' => ['timeout' => '']]), false);

    expect($connect->connect())->toBeInstanceOf(SSH2::class);
    expect($connect->timeout)->toBe(0);
});

test('ssh connect accepts a template window size given as strings', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['options' => ['setWindowSize' => ['132', '50']]]), false);

    $connection = $connect->connect();

    $reflection = new ReflectionClass($connection);
    expect($reflection->getProperty('windowColumns')->getValue($connection))->toBe(132);
    expect($reflection->getProperty('windowRows')->getValue($connection))->toBe(50);
});

test('ssh connect ignores a malformed window size', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['options' => ['setWindowSize' => [132]]]), false);

    expect($connect->connect())->toBeInstanceOf(SSH2::class);
});
