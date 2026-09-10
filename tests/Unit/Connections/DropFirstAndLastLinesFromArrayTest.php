<?php

use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;
use App\Http\Controllers\Connections\Telnet\Read as TelnetRead;
use App\Http\Controllers\Connections\Telnet\SendCommand as TelnetSendCommand;

/**
 * @param  array<int, string>  $lines
 * @return array<int, string>
 */
function dropViaSsh(array $lines): array
{
    $reflection = new ReflectionClass(SshSendCommand::class);
    $sendCommand = $reflection->newInstanceWithoutConstructor();

    $data = $reflection->getProperty('data');
    $data->setValue($sendCommand, $lines);

    $reflection->getMethod('dropFirstAndLastLinesFromArray')->invoke($sendCommand);

    return $data->getValue($sendCommand);
}

/**
 * @param  array<int, string>  $lines
 * @return array<int, string>
 */
function dropViaTelnet(array $lines): array
{
    $readReflection = new ReflectionClass(TelnetRead::class);
    $read = $readReflection->newInstanceWithoutConstructor();

    $readData = $readReflection->getProperty('data');
    $readData->setValue($read, $lines);

    $reflection = new ReflectionClass(TelnetSendCommand::class);
    $sendCommand = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('read')->setValue($sendCommand, $read);

    $reflection->getMethod('dropFirstAndLastLinesFromArray')->invoke($sendCommand);

    return $readData->getValue($read);
}

test('ssh keeps single line output', function () {
    expect(dropViaSsh(['hostname router1']))->toBe(['hostname router1']);
});

test('ssh keeps two line output', function () {
    expect(dropViaSsh(['show run', 'hostname router1']))->toBe(['show run', 'hostname router1']);
});

test('ssh strips command echo and prompt when content survives', function () {
    expect(dropViaSsh(['show run', 'hostname router1', 'router1#']))->toBe(['hostname router1']);
});

test('ssh strips only the outer lines of longer output', function () {
    expect(dropViaSsh(['show run', 'version 15.2', 'hostname router1', 'interface Gi0/0', 'router1#']))->toBe(['version 15.2', 'hostname router1', 'interface Gi0/0']);
});

test('ssh handles empty output', function () {
    expect(dropViaSsh([]))->toBe([]);
});

test('telnet keeps single line output', function () {
    expect(dropViaTelnet(['hostname router1']))->toBe(['hostname router1']);
});

test('telnet keeps two line output', function () {
    expect(dropViaTelnet(['show run', 'hostname router1']))->toBe(['show run', 'hostname router1']);
});

test('telnet strips command echo and prompt when content survives', function () {
    expect(dropViaTelnet(['show run', 'hostname router1', 'router1#']))->toBe(['hostname router1']);
});

test('telnet strips only the outer lines of longer output', function () {
    expect(dropViaTelnet(['show run', 'version 15.2', 'hostname router1', 'interface Gi0/0', 'router1#']))->toBe(['version 15.2', 'hostname router1', 'interface Gi0/0']);
});

test('telnet handles empty output', function () {
    expect(dropViaTelnet([]))->toBe([]);
});
