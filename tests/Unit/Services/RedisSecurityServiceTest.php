<?php

use App\Services\Utilities\RedisSecurityService;
use Predis\Client;
use Predis\Response\ServerException;

/**
 * An in-memory stand-in for a Redis server. Lets the service be exercised end to
 * end without ever touching a live instance (this box shares one Redis across
 * several apps, so a real requirepass here would lock them out).
 */
class FakeRedisServer
{
    public bool $up = true;
    public ?string $requirepass = null;
    public string $bind = '127.0.0.1';
    public string $protectedMode = 'yes';
    public bool $rewriteFails = false;
}

/**
 * A fake predis client bound to a FakeRedisServer. Enforces the same auth rules
 * a real server would so the service's detection and apply logic are genuinely
 * tested.
 */
class FakeRedisClient extends Client
{
    public function __construct(private FakeRedisServer $server, private ?string $password)
    {
        // Intentionally skip parent constructor: no real connection is made.
    }

    private function guard(): void
    {
        if (! $this->server->up) {
            throw new RuntimeException('Connection refused');
        }

        if ($this->server->requirepass !== null) {
            if ($this->password === null) {
                throw new ServerException('NOAUTH Authentication required.');
            }
            if ($this->password !== $this->server->requirepass) {
                throw new ServerException('WRONGPASS invalid username-password pair');
            }
        } elseif ($this->password !== null && $this->password !== '') {
            throw new ServerException('ERR Client sent AUTH, but no password is set');
        }
    }

    public function ping(?string $message = null): string
    {
        $this->guard();

        return 'PONG';
    }

    public function executeRaw(array $arguments, &$error = null)
    {
        $this->guard();

        $command = strtoupper($arguments[0] ?? '');
        $sub = strtoupper($arguments[1] ?? '');

        if ($command === 'CONFIG' && $sub === 'SET' && strtolower($arguments[2] ?? '') === 'requirepass') {
            $this->server->requirepass = $arguments[3];

            return 'OK';
        }

        if ($command === 'CONFIG' && $sub === 'REWRITE') {
            if ($this->server->rewriteFails) {
                throw new ServerException('ERR The server is running without a config file');
            }

            return 'OK';
        }

        if ($command === 'CONFIG' && $sub === 'GET') {
            $key = strtolower($arguments[2] ?? '');

            return match ($key) {
                'bind' => ['bind', $this->server->bind],
                'protected-mode' => ['protected-mode', $this->server->protectedMode],
                default => [],
            };
        }

        return 'OK';
    }
}

function makeService(FakeRedisServer $server, string $envContent = "REDIS_PASSWORD=null\n"): array
{
    $envPath = tempnam(sys_get_temp_dir(), 'redissec_');
    file_put_contents($envPath, $envContent);

    $factory = fn (?string $password): FakeRedisClient => new FakeRedisClient($server, $password);

    return [new RedisSecurityService($envPath, $factory), $envPath];
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir() . '/redissec_*') as $leftover) {
        @unlink($leftover);
    }
});

test('passwordIsRequired is false for an open server and true once a password is set', function () {
    $server = new FakeRedisServer;
    [$service] = makeService($server);

    expect($service->passwordIsRequired())->toBeFalse();

    $server->requirepass = 'secret';
    expect($service->passwordIsRequired())->toBeTrue();
});

test('status reports insecure for an open server and secure once a password is set', function () {
    $server = new FakeRedisServer;
    [$service] = makeService($server);

    $open = $service->status();
    expect($open['reachable'])->toBeTrue();
    expect($open['password_required'])->toBeFalse();
    expect($open['secure'])->toBeFalse();
    expect($open['bind'])->toBe('127.0.0.1');
    expect($open['protected_mode'])->toBe('yes');

    $server->requirepass = 'secret';
    $secured = $service->status();
    expect($secured['password_required'])->toBeTrue();
    expect($secured['secure'])->toBeTrue();
});

test('status reports unreachable when the server is down', function () {
    $server = new FakeRedisServer;
    $server->up = false;
    [$service] = makeService($server);

    $status = $service->status();
    expect($status['reachable'])->toBeFalse();
    expect($status['secure'])->toBeFalse();
    expect($status['password_required'])->toBeNull();
});

test('generateSecret returns a unique 64 character hex string', function () {
    $server = new FakeRedisServer;
    [$service] = makeService($server);

    $a = $service->generateSecret();
    $b = $service->generateSecret();

    expect($a)->toMatch('/^[0-9a-f]{64}$/');
    expect($a)->not->toBe($b);
});

test('currentEnvPassword treats null and empty as no password', function () {
    [$nullService] = makeService(new FakeRedisServer, "REDIS_PASSWORD=null\n");
    expect($nullService->currentEnvPassword())->toBeNull();

    [$emptyService] = makeService(new FakeRedisServer, "REDIS_PASSWORD=\n");
    expect($emptyService->currentEnvPassword())->toBeNull();

    [$setService] = makeService(new FakeRedisServer, "REDIS_PASSWORD=abc123\n");
    expect($setService->currentEnvPassword())->toBe('abc123');
});

test('secure writes the password to .env and requires it on the live instance', function () {
    $server = new FakeRedisServer;
    [$service, $envPath] = makeService($server);

    $result = $service->secure();

    expect($result['action'])->toBe('secured');
    expect($result['changed'])->toBeTrue();
    expect($result['persisted'])->toBeTrue();
    expect($server->requirepass)->toBe($result['secret']);

    $env = file_get_contents($envPath);
    expect($env)->toContain('REDIS_PASSWORD=' . $result['secret']);
    expect($service->passwordIsRequired())->toBeTrue();
});

test('secure is idempotent when already secured and in sync', function () {
    $server = new FakeRedisServer;
    $server->requirepass = 'existing-secret';
    [$service] = makeService($server, "REDIS_PASSWORD=existing-secret\n");

    $result = $service->secure();

    expect($result['action'])->toBe('already_secured');
    expect($result['changed'])->toBeFalse();
    expect($server->requirepass)->toBe('existing-secret');
});

test('secure throws on a desync without force', function () {
    $server = new FakeRedisServer; // open, no requirepass
    [$service] = makeService($server, "REDIS_PASSWORD=stale-secret\n");

    expect(fn () => $service->secure())->toThrow(RuntimeException::class, 'desync');
});

test('secure with force regenerates over an existing password', function () {
    $server = new FakeRedisServer;
    $server->requirepass = 'old-secret';
    [$service, $envPath] = makeService($server, "REDIS_PASSWORD=old-secret\n");

    $result = $service->secure(force: true);

    expect($result['action'])->toBe('secured');
    expect($result['secret'])->not->toBe('old-secret');
    expect($server->requirepass)->toBe($result['secret']);
    expect(file_get_contents($envPath))->toContain('REDIS_PASSWORD=' . $result['secret']);
});

test('secure rolls back the .env write when the live apply fails', function () {
    $server = new FakeRedisServer;
    $server->up = false; // CONFIG SET will fail
    [$service, $envPath] = makeService($server);

    expect(fn () => $service->secure())->toThrow(RuntimeException::class, 'rolled back');

    expect(file_get_contents($envPath))->toContain('REDIS_PASSWORD=null');
    expect(file_get_contents($envPath))->not->toMatch('/REDIS_PASSWORD=[0-9a-f]{64}/');
});

test('secure reports unpersisted when CONFIG REWRITE fails but does not roll back', function () {
    $server = new FakeRedisServer;
    $server->rewriteFails = true;
    [$service, $envPath] = makeService($server);

    $result = $service->secure();

    expect($result['action'])->toBe('secured');
    expect($result['persisted'])->toBeFalse();
    expect($server->requirepass)->toBe($result['secret']);
    expect(file_get_contents($envPath))->toContain('REDIS_PASSWORD=' . $result['secret']);
});
