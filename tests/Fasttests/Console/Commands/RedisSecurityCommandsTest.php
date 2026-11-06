<?php

use App\Services\Utilities\RedisSecurityService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

/**
 * The commands are tested against a mocked service so the suite never touches a
 * live Redis. The service's own detection/apply logic is covered by
 * tests/Unit/Services/RedisSecurityServiceTest.php.
 */
function mockRedisSecurity(Closure $expectations): void
{
    test()->instance(
        RedisSecurityService::class,
        Mockery::mock(RedisSecurityService::class, $expectations)
    );
}

function status(array $overrides = []): array
{
    return array_merge([
        'reachable' => true,
        'password_required' => true,
        'app_authenticated' => true,
        'secure' => true,
        'bind' => '127.0.0.1',
        'protected_mode' => 'yes',
        'detail' => 'Redis requires authentication.',
    ], $overrides);
}

test('check command exits 0 when Redis requires a password', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(status());
    });

    $this->artisan('rconfig:redis-security-check')->assertExitCode(0);
});

test('check command exits 1 when Redis has no password', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(status([
            'password_required' => false,
            'secure' => false,
        ]));
    });

    $this->artisan('rconfig:redis-security-check')
        ->expectsOutputToContain('rconfig:redis-secure')
        ->assertExitCode(1);
});

test('check command exits 2 when Redis is unreachable', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(status([
            'reachable' => false,
            'password_required' => null,
            'app_authenticated' => false,
            'secure' => false,
        ]));
    });

    $this->artisan('rconfig:redis-security-check')->assertExitCode(2);
});

test('check command emits JSON with --json', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(status());
    });

    $this->artisan('rconfig:redis-security-check --json')
        ->expectsOutputToContain('"password_required": true')
        ->assertExitCode(0);
});

test('secure command applies and reports success', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->with(false)->andReturn([
            'action' => 'secured',
            'changed' => true,
            'persisted' => true,
            'secret' => str_repeat('a', 64),
        ]);
    });

    $this->artisan('rconfig:redis-secure --no-restart')
        ->expectsOutputToContain('Skipping consumer restart')
        ->assertExitCode(0);
});

test('secure command is idempotent when already secured', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->with(false)->andReturn([
            'action' => 'already_secured',
            'changed' => false,
            'persisted' => true,
            'secret' => null,
        ]);
    });

    $this->artisan('rconfig:redis-secure --no-restart')
        ->expectsOutputToContain('already secured')
        ->assertExitCode(0);
});

test('secure command fails closed when the service throws', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->andThrow(new RuntimeException('desync detected'));
    });

    $this->artisan('rconfig:redis-secure --no-restart')
        ->expectsOutputToContain('desync detected')
        ->assertExitCode(1);
});

test('secure command warns when the password cannot be persisted', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->with(false)->andReturn([
            'action' => 'secured',
            'changed' => true,
            'persisted' => false,
            'secret' => str_repeat('b', 64),
        ]);
    });

    $this->artisan('rconfig:redis-secure --no-restart')
        ->expectsOutputToContain('not persisted')
        ->assertExitCode(0);
});

test('secure --dry-run changes nothing and reports the plan', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('currentEnvPassword')->once()->andReturnNull();
        $mock->shouldNotReceive('secure');
    });

    $this->artisan('rconfig:redis-secure --dry-run')
        ->expectsOutputToContain('[dry run]')
        ->assertExitCode(0);
});

/**
 * Replace a consumer restart command with a stand-in so the secure command's
 * restart path runs without reaching any Redis. Records each call.
 *
 * @param  array<int, string>  $calls
 */
function standInRestartCommand(string $name, array &$calls, ?Throwable $failure = null): void
{
    $command = new class($name, $calls, $failure) extends Command
    {
        /**
         * @param  array<int, string>  $calls
         */
        public function __construct(string $name, private array &$calls, private ?Throwable $failure)
        {
            $this->signature = $name;
            parent::__construct();
        }

        public function handle(): int
        {
            $this->calls[] = $this->getName();

            if ($this->failure !== null) {
                throw $this->failure;
            }

            return self::SUCCESS;
        }
    };

    app(Kernel::class)->registerCommand($command);
}

function securedResult(): array
{
    return [
        'action' => 'secured',
        'changed' => true,
        'persisted' => true,
        'secret' => str_repeat('c', 64),
    ];
}

test('secure command restarts queue workers and Horizon', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->with(false)->andReturn(securedResult());
    });

    $calls = [];
    standInRestartCommand('queue:restart', $calls);
    standInRestartCommand('horizon:terminate', $calls);

    $this->artisan('rconfig:redis-secure')
        ->expectsOutputToContain('Broadcast queue restart signal')
        ->expectsOutputToContain('Signalled Horizon to restart')
        ->assertExitCode(0);

    expect($calls)->toBe(['queue:restart', 'horizon:terminate']);
});

test('secure command only warns when queue:restart fails and still signals Horizon', function () {
    mockRedisSecurity(function ($mock) {
        $mock->shouldReceive('secure')->once()->with(false)->andReturn(securedResult());
    });

    $calls = [];
    standInRestartCommand('queue:restart', $calls, new RuntimeException('NOAUTH Authentication required.'));
    standInRestartCommand('horizon:terminate', $calls);

    $this->artisan('rconfig:redis-secure')
        ->expectsOutputToContain('Could not broadcast the queue restart signal: NOAUTH Authentication required.')
        ->expectsOutputToContain('Signalled Horizon to restart')
        ->expectsOutputToContain('Redis is now secured')
        ->assertExitCode(0);

    expect($calls)->toBe(['queue:restart', 'horizon:terminate']);
});
