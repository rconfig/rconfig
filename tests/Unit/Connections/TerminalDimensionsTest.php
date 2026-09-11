<?php

use App\Http\Controllers\Connections\SSH\Connect as SshConnect;
use App\Http\Controllers\Connections\SSH\SendCommand as SshSendCommand;
use phpseclib3\File\ANSI;
use Tests\Unit\Connections\DeviceParamsBuilder;

/**
 * @return array{0: int, 1: int} the ANSI object's max x and max y
 */
function ansiDimensions(ANSI $ansi): array
{
    $reflection = new ReflectionClass($ansi);

    return [
        $reflection->getProperty('max_x')->getValue($ansi),
        $reflection->getProperty('max_y')->getValue($ansi),
    ];
}

function ansiForConnectionObject(object $connectionObj): ANSI
{
    $reflection = new ReflectionClass(SshSendCommand::class);
    $sendCommand = $reflection->newInstanceWithoutConstructor();
    $reflection->getProperty('connectionObj')->setValue($sendCommand, $connectionObj);

    return $reflection->getMethod('ansiForSession')->invoke($sendCommand);
}

test('connect does not bolt a dynamic property onto the ssh object', function () {
    $connect = new SshConnect(
        DeviceParamsBuilder::forSsh(['options' => ['setTerminalDimensions' => [132, 50]]]),
        false
    );

    $connection = $connect->connect();

    expect(property_exists($connection, 'setTerminalDimensions'))->toBeFalse('setTerminalDimensions must not be set as a dynamic property on the phpseclib SSH2 object.');
});

test('connect keeps the template dimensions on the connection object', function () {
    $connect = new SshConnect(
        DeviceParamsBuilder::forSsh(['options' => ['setTerminalDimensions' => [132, 50]]]),
        false
    );

    $connect->connect();

    expect($connect->setTerminalDimensions)->toBe([132, 50]);
});

test('ansi session uses the template dimensions', function () {
    $ansi = ansiForConnectionObject((object) ['setTerminalDimensions' => [132, 50]]);

    expect(ansiDimensions($ansi))->toBe([131, 49]);
});

test('ansi session uses the template dimensions given as strings', function () {
    $ansi = ansiForConnectionObject((object) ['setTerminalDimensions' => ['132', '50']]);

    expect(ansiDimensions($ansi))->toBe([131, 49]);
});

test('ansi session falls back to the default dimensions when the template omits them', function () {
    $ansi = ansiForConnectionObject((object) ['setTerminalDimensions' => null]);

    expect(ansiDimensions($ansi))->toBe([79, 23]);
});

test('ansi session ignores a malformed dimensions value', function () {
    $ansi = ansiForConnectionObject((object) ['setTerminalDimensions' => [132]]);

    expect(ansiDimensions($ansi))->toBe([79, 23]);
});
