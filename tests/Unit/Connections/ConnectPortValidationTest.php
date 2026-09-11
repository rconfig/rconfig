<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\Telnet\Connect as TelnetConnect;
use Tests\Unit\Connections\DeviceParamsBuilder;

function resolveSshPort(mixed $port): int
{
    $reflection = new ReflectionClass(SshConnect::class);
    $connect = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('port')->setValue($connect, $port);

    return $reflection->getMethod('sshPortValidOrDefault')->invoke($connect);
}

function resolveTelnetPort(mixed $port): int
{
    $reflection = new ReflectionClass(TelnetConnect::class);
    $connect = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('port')->setValue($connect, $port);

    return $reflection->getMethod('telnetPortValidOrDefault')->invoke($connect);
}

test('ssh keeps a valid port', function () {
    expect(resolveSshPort(2222))->toBe(2222);
});

test('ssh keeps a valid port given as a string', function () {
    expect(resolveSshPort('22'))->toBe(22);
});

test('ssh defaults a null port', function () {
    expect(resolveSshPort(null))->toBe(22);
});

test('ssh defaults an empty port', function () {
    expect(resolveSshPort(''))->toBe(22);
});

test('ssh defaults a zero or negative port', function () {
    expect(resolveSshPort(0))->toBe(22);
    expect(resolveSshPort(-1))->toBe(22);
});

test('ssh defaults a port above the valid range', function () {
    expect(resolveSshPort(70000))->toBe(22);
});

test('telnet keeps a valid port', function () {
    expect(resolveTelnetPort(2323))->toBe(2323);
});

test('telnet keeps a valid port given as a string', function () {
    expect(resolveTelnetPort('23'))->toBe(23);
});

test('telnet defaults a null port', function () {
    expect(resolveTelnetPort(null))->toBe(23);
});

test('telnet defaults an empty port', function () {
    expect(resolveTelnetPort(''))->toBe(23);
});

test('telnet defaults a zero or negative port', function () {
    expect(resolveTelnetPort(0))->toBe(23);
    expect(resolveTelnetPort(-1))->toBe(23);
});

test('telnet defaults a port above the valid range', function () {
    expect(resolveTelnetPort(70000))->toBe(23);
});

test('ssh connect dials the resolved port', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['connect' => ['port' => 0]]), false);
    $connect->connect();

    expect($connect->port)->toBe(22);
});

test('ssh connect keeps a valid port', function () {
    $connect = new SshConnect(DeviceParamsBuilder::forSsh(['connect' => ['port' => 2222]]), false);
    $connect->connect();

    expect($connect->port)->toBe(2222);
});
