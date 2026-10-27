<?php

namespace Tests\Unit\Services;

use Predis\Client;
use Predis\Response\ServerException;
use RuntimeException;

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
