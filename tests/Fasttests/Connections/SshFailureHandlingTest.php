<?php

use App\Http\Controllers\Connections\SSH\Login;
use App\Http\Controllers\Connections\SSHConnectionManager;
use App\Models\Device;
use App\Models\DeviceCredentials;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Tests\Unit\Connections\DeviceParamsBuilder;

/**
 * Every test here stays on the loopback interface: a port that was bound and then
 * released (nothing listening, so the dial is refused), or a local listener that
 * accepts the TCP connection and never sends an SSH banner. No device is contacted.
 */
beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function closedLoopbackPort(): int
{
    $server = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
    fclose($server);

    return $port;
}

/**
 * @param  array<string, array<string, mixed>>  $overrides
 */
function sshParamsForDevice(Device $device, int $port, array $overrides = []): object
{
    return DeviceParamsBuilder::forSsh(array_replace_recursive([
        'connect' => ['port' => $port, 'timeout' => 2],
        'deviceparams' => [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'device_password' => 'S3cr3t-Device-Pass',
        ],
    ], $overrides));
}

function latestConnectionActivity(): ?Activity
{
    return Activity::query()->where('event_type', 'connection')->latest('id')->first();
}

test('a refused ssh connection is reported as a device failure, not thrown', function () {
    $device = Device::factory()->create();

    $result = (new SSHConnectionManager(sshParamsForDevice($device, closedLoopbackPort()), false))->SSHConnectionAndOutput();

    expect($result)->toBeArray()->toHaveKey('failure');
    expect($device->fresh()->status)->toBe(0);
});

test('a refused ssh connection logs the reason without the device password', function () {
    $device = Device::factory()->create();

    (new SSHConnectionManager(sshParamsForDevice($device, closedLoopbackPort()), false))->SSHConnectionAndOutput();

    $logged = Activity::query()->where('device_id', $device->id)->pluck('description')->implode("\n");

    expect($logged)->toContain('Cannot connect to');
    expect($logged)->not->toContain('S3cr3t-Device-Pass');
});

/**
 * phpseclib (3 and 4 alike) falls back to a blocking read once its select times out,
 * so the banner wait is really bounded by default_socket_timeout. Pin it low here so
 * the test is quick; what matters is that the stalled handshake fails this device.
 */
test('an ssh server that never sends a banner fails the device, not the job', function () {
    $device = Device::factory()->create();
    $listener = stream_socket_server('tcp://127.0.0.1:0');
    $port = (int) substr(strrchr(stream_socket_get_name($listener, false), ':'), 1);
    $socketTimeout = ini_set('default_socket_timeout', '2');

    try {
        $result = (new SSHConnectionManager(sshParamsForDevice($device, $port, ['connect' => ['timeout' => 1]]), false))->SSHConnectionAndOutput();
    } finally {
        ini_set('default_socket_timeout', (string) $socketTimeout);
        fclose($listener);
    }

    expect($result)->toBeArray()->toHaveKey('failure');
    expect(Activity::query()->where('device_id', $device->id)->pluck('description')->implode("\n"))->toContain('identification string');
});

test('password login passes the username and password to phpseclib', function () {
    $connection = fakeSshLogin(true);
    $login = loginFor((object) ['username' => 'rconfig', 'password' => 'S3cr3t-Device-Pass'], $connection);

    expect($login->loginErrorCheck())->toBeTrue();
    expect($connection->calls)->toBe([['rconfig', 'S3cr3t-Device-Pass']]);
});

test('a device with no stored password tries the none method instead of throwing', function () {
    $connection = fakeSshLogin(false);
    $device = Device::factory()->create();
    $login = loginFor((object) ['username' => 'rconfig', 'password' => null, 'device_id' => $device->id], $connection);

    expect($login->loginErrorCheck())->toBeFalse();
    expect($connection->calls)->toBe([['rconfig']]);
});

test('a failed password login is logged without the password', function () {
    $device = Device::factory()->create();
    $login = loginFor((object) ['username' => 'rconfig', 'password' => 'S3cr3t-Device-Pass', 'device_id' => $device->id], fakeSshLogin(false));

    expect($login->loginErrorCheck())->toBeFalse();

    $logged = latestConnectionActivity()?->description;
    expect($logged)->toContain('Authentication Failed');
    expect($logged)->not->toContain('S3cr3t-Device-Pass');
});

test('key login with no loadable key fails the device and logs no key material', function () {
    Log::spy();
    $device = Device::factory()->create();
    $cred = DeviceCredentials::factory()->create();
    $login = loginFor((object) [
        'username' => 'rconfig',
        'password' => 'S3cr3t-Device-Pass',
        'device_id' => $device->id,
        'device_cred_id' => $cred->id,
        'sshPrivKey' => 'on',
    ], fakeSshLogin(true));

    $authenticate = (new ReflectionClass(Login::class))->getMethod('authenticateWithKey');

    expect($authenticate->invoke($login))->toBeFalse();
    Log::shouldHaveReceived('error')->withArgs(function (string $message, array $context) {
        return str_contains($message, 'Authentication Failed using SSH Private key')
            && array_keys($context) === ['exception', 'message']
            && ! str_contains(json_encode($context), 'S3cr3t-Device-Pass');
    })->once();
});

function fakeSshLogin(bool $succeeds): object
{
    return new class($succeeds)
    {
        /** @var list<list<mixed>> */
        public array $calls = [];

        public function __construct(private bool $succeeds) {}

        public function login(string $username, mixed ...$credentials): bool
        {
            $this->calls[] = [$username, ...$credentials];

            return $this->succeeds;
        }
    };
}

function loginFor(object $values, object $connection): Login
{
    $connectionObj = (object) array_merge([
        'connection' => $connection,
        'hostname' => '127.0.0.1',
        'device_id' => 0,
        'device_cred_id' => null,
        'sshPrivKey' => null,
        'hpAnyKeyStatus' => 'off',
    ], (array) $values);

    return new Login($connectionObj);
}
