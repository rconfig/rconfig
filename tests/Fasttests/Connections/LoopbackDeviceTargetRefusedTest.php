<?php

use App\Http\Controllers\Connections\SSHConnectionManager;
use App\Http\Controllers\Connections\TelnetConnectionManager;
use App\Models\Device;
use Spatie\Activitylog\Models\Activity;
use Tests\Traits\FakesHostResolver;
use Tests\Unit\Connections\DeviceParamsBuilder;

uses(FakesHostResolver::class);

/**
 * GHSA-p5q7-2wwp-q42f: the connectors dialled device_ip as written, so any name or
 * shorthand form that resolved to loopback reached a local port (Redis on 6379).
 * Each test points a device at a real listener on 127.0.0.1 and proves the
 * connector refuses before dialling: the listener never sees a connection.
 */
beforeEach(function () {
    $this->beginTransaction();
    $this->listener = stream_socket_server('tcp://127.0.0.1:0');
    $this->listenerPort = (int) substr(strrchr(stream_socket_get_name($this->listener, false), ':'), 1);
});

afterEach(function () {
    fclose($this->listener);
    $this->rollBackTransaction();
});

function listenerWasDialled(mixed $listener): bool
{
    $client = @stream_socket_accept($listener, 0.2);

    if ($client === false) {
        return false;
    }

    fclose($client);

    return true;
}

/**
 * @return array<string, array<string, mixed>>
 */
function loopbackTargetOverrides(Device $device, string $target, int $port): array
{
    return [
        'connect' => ['timeout' => 1],
        'deviceparams' => [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'device_ip' => $target,
            'device_port_override' => $port,
        ],
    ];
}

function refusalLogged(Device $device): bool
{
    return Activity::query()
        ->where('device_id', $device->id)
        ->where('description', 'like', '%points at this rConfig server%')
        ->exists();
}

dataset('loopback targets', [
    'literal loopback saved before RCO-1813' => '127.0.0.1',
    'localhost' => 'localhost',
    'shorthand ipv4' => '127.1',
    'decimal ipv4' => '2130706433',
    'name with a loopback A record' => 'rebind.example.com',
]);

test('telnet refuses a device that resolves to this server', function (string $target) {
    $this->fakeHostResolver(['rebind.example.com' => ['127.0.0.1'], '127.1' => ['127.0.0.1'], '2130706433' => ['127.0.0.1'], 'localhost' => ['127.0.0.1']]);
    $device = Device::factory()->create(['status' => 1]);

    $params = DeviceParamsBuilder::forTelnet(loopbackTargetOverrides($device, $target, $this->listenerPort));
    $result = (new TelnetConnectionManager($params, false))->telnetConnectionAndOutput();

    expect($result)->toBeFalse();
    expect(listenerWasDialled($this->listener))->toBeFalse();
    expect($device->fresh()->status)->toBe(0);
    expect(refusalLogged($device))->toBeTrue();
})->with('loopback targets');

test('ssh refuses a device that resolves to this server', function (string $target) {
    $this->fakeHostResolver(['rebind.example.com' => ['127.0.0.1'], '127.1' => ['127.0.0.1'], '2130706433' => ['127.0.0.1'], 'localhost' => ['127.0.0.1']]);
    $device = Device::factory()->create(['status' => 1]);

    $params = DeviceParamsBuilder::forSsh(loopbackTargetOverrides($device, $target, $this->listenerPort));
    $result = (new SSHConnectionManager($params, false))->SSHConnectionAndOutput();

    expect($result)->toBeFalse();
    expect(listenerWasDialled($this->listener))->toBeFalse();
    expect($device->fresh()->status)->toBe(0);
    expect(refusalLogged($device))->toBeTrue();
})->with('loopback targets');

test('the shorthand loopback forms are refused by the real resolver too', function (string $target) {
    $device = Device::factory()->create();

    $params = DeviceParamsBuilder::forTelnet(loopbackTargetOverrides($device, $target, $this->listenerPort));

    expect((new TelnetConnectionManager($params, false))->telnetConnectionAndOutput())->toBeFalse();
    expect(listenerWasDialled($this->listener))->toBeFalse();
})->with(['127.1', '2130706433', '0177.0.0.1']);

/*
 * The connector dials the address it checked, not the name, so a DNS answer that changes
 * between the check and the dial cannot steer the connection onto loopback.
 */
test('the connector dials the address it resolved, not the name', function () {
    $resolver = $this->fakeHostResolver(['router.example.com' => ['192.0.2.1']]);
    $device = Device::factory()->create();

    $params = DeviceParamsBuilder::forTelnet(loopbackTargetOverrides($device, 'router.example.com', $this->listenerPort));
    (new TelnetConnectionManager($params, false))->telnetConnectionAndOutput();

    // Resolved once and dialled by address. Other lookups are the Redis host, when it is a name.
    expect(array_count_values($resolver->lookups)['router.example.com'] ?? 0)->toBe(1);
    expect(listenerWasDialled($this->listener))->toBeFalse();
    expect(refusalLogged($device))->toBeFalse();
});

test('a device name that does not resolve is reported, not dialled', function () {
    $this->fakeHostResolver();
    $device = Device::factory()->create();

    $params = DeviceParamsBuilder::forTelnet(loopbackTargetOverrides($device, 'gone.example.com', $this->listenerPort));

    expect((new TelnetConnectionManager($params, false))->telnetConnectionAndOutput())->toBeFalse();
    expect(Activity::query()->where('device_id', $device->id)->pluck('description')->implode("\n"))->toContain('could not be resolved');
});
