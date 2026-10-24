<?php

namespace App\Services\Utilities;

use App\Console\Commands\EnvironmentSetCommand;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Predis\Client;
use Predis\Connection\ConnectionException;
use Predis\Response\ServerException;
use RuntimeException;

/**
 * Detects and remediates the single real control on Core's Redis instance: a
 * connection password (requirepass). Core runs Horizon workers that deserialise
 * queue payloads, so an unauthenticated Redis that any local process can write
 * to is a code-execution path. This service is the shared brain behind the
 * check command, the fix command, the health check, and the installer.
 */
class RedisSecurityService
{
    /**
     * Length in bytes of the generated secret before hex encoding. 32 bytes
     * yields a 64 character hex string, matching `openssl rand -hex 32`.
     */
    private const SECRET_BYTES = 32;

    /**
     * The .env key that carries the Redis password.
     */
    private const ENV_KEY = 'REDIS_PASSWORD';

    /**
     * Values in .env that mean "no password", normalised case-insensitively.
     *
     * @var array<int, string>
     */
    private const EMPTY_VALUES = ['', 'null'];

    /**
     * @param  string|null  $envPath  Overridable for tests. Defaults to the app .env.
     * @param  (\Closure(?string): Client)|null  $clientFactory  Overridable
     *                                                           for tests so the Redis calls can be exercised without a live server.
     */
    public function __construct(
        private ?string $envPath = null,
        private ?\Closure $clientFactory = null,
    ) {
        $this->envPath ??= App::environmentFilePath();
    }

    /**
     * Is Redis currently demanding a password? Determined by an unauthenticated
     * PING: a password-protected server answers NOAUTH, an open one answers PONG.
     *
     * @throws ConnectionException when Redis is unreachable.
     */
    public function passwordIsRequired(): bool
    {
        try {
            $this->makeClient(null)->ping();

            return false;
        } catch (ServerException $e) {
            if (str_contains($e->getMessage(), 'NOAUTH')) {
                return true;
            }

            throw $e;
        }
    }

    /**
     * Can we authenticate against Redis with the given password? Used both to
     * verify a freshly applied secret and to detect a .env/Redis desync without
     * relying on the app's cached connection state.
     */
    public function canAuthenticateWith(?string $password): bool
    {
        try {
            $this->makeClient($password)->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Does the app, as currently configured, reach Redis? Reflects the live
     * .env/config state via the framework connection.
     */
    public function appCanAuthenticate(): bool
    {
        try {
            Redis::connection()->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Listener bind and protected-mode, for context only. This is never an
     * all-clear: the loopback bind is not a boundary while the device Telnet
     * path can open sockets to 127.0.0.1. Best effort; returns 'unknown' rather
     * than throwing.
     *
     * @return array{bind: string, protected_mode: string}
     */
    public function bindSummary(): array
    {
        try {
            $password = $this->passwordIsRequired() ? $this->currentEnvPassword() : null;
            $client = $this->makeClient($password);

            $bind = $client->executeRaw(['CONFIG', 'GET', 'bind']);
            $protectedMode = $client->executeRaw(['CONFIG', 'GET', 'protected-mode']);

            return [
                'bind' => $this->configValue($bind) ?: 'unknown',
                'protected_mode' => $this->configValue($protectedMode) ?: 'unknown',
            ];
        } catch (\Throwable) {
            return ['bind' => 'unknown', 'protected_mode' => 'unknown'];
        }
    }

    /**
     * Structured security status for the check command, the health check, and
     * the UI. `secure` is true only when a password is required.
     *
     * @return array{
     *     reachable: bool,
     *     password_required: bool|null,
     *     app_authenticated: bool,
     *     secure: bool,
     *     bind: string,
     *     protected_mode: string,
     *     detail: string
     * }
     */
    public function status(): array
    {
        $reachable = true;
        $passwordRequired = null;

        try {
            $passwordRequired = $this->passwordIsRequired();
        } catch (ConnectionException) {
            $reachable = false;
        } catch (\Throwable) {
            $reachable = false;
        }

        $context = $reachable ? $this->bindSummary() : ['bind' => 'unknown', 'protected_mode' => 'unknown'];
        $secure = $reachable && $passwordRequired === true;

        return [
            'reachable' => $reachable,
            'password_required' => $passwordRequired,
            'app_authenticated' => $reachable ? $this->appCanAuthenticate() : false,
            'secure' => $secure,
            'bind' => $context['bind'],
            'protected_mode' => $context['protected_mode'],
            'detail' => $this->describe($reachable, $passwordRequired),
        ];
    }

    /**
     * Generate a strong Redis password, equivalent to `openssl rand -hex 32`.
     */
    public function generateSecret(): string
    {
        return bin2hex(random_bytes(self::SECRET_BYTES));
    }

    /**
     * Apply a Redis password end to end: write .env, rebuild the config cache,
     * require it on the live instance, persist it, and refresh this process's
     * connection so the caller can restart consumers. Does NOT restart workers;
     * that is the command's final step. Fails closed and rolls back the .env
     * write if the live Redis will not accept the change.
     *
     * Idempotent: a box already secured with the .env password returns
     * 'already_secured' and changes nothing unless $force is given.
     *
     * @return array{action: string, changed: bool, persisted: bool, secret: string|null}
     *
     * @throws RuntimeException on a desync without --force, or a failed apply.
     */
    public function secure(bool $force = false): array
    {
        $current = $this->currentEnvPassword();

        if ($current !== null && ! $force) {
            if ($this->canAuthenticateWith($current)) {
                return ['action' => 'already_secured', 'changed' => false, 'persisted' => true, 'secret' => null];
            }

            throw new RuntimeException(
                'REDIS_PASSWORD is set in .env but the live Redis does not accept it (desync). '
                . 'Re-run with --force to regenerate, or align .env with the live requirepass manually.'
            );
        }

        $secret = $this->generateSecret();
        $previousRaw = $this->readRawEnvValue();

        // 1. Write .env first so a restart of any consumer picks up the password.
        $this->writeEnvPassword($secret);

        // 2. Rebuild config so env() is seen at runtime. Production caches config,
        //    where env() otherwise returns null and predis would skip AUTH. Only
        //    touch the cache when it actually exists; an uncached box reads .env
        //    live and new worker processes pick the password up on their own.
        $wasCached = App::configurationIsCached();

        if ($wasCached) {
            Artisan::call('config:clear');
        }

        // 3. Require it on the live instance, connecting with the OLD credential.
        try {
            $this->makeClient($current)->executeRaw(['CONFIG', 'SET', 'requirepass', $secret]);
        } catch (\Throwable $e) {
            // Roll back so .env and Redis never disagree, which would break queues.
            $this->restoreRawEnvValue($previousRaw);

            if ($wasCached) {
                $this->rebuildConfigCache();
            }

            throw new RuntimeException('Failed to set requirepass on Redis; rolled back .env. ' . $e->getMessage(), 0, $e);
        }

        // 4. Persist across a Redis restart. If this fails the password is live
        //    but not persisted: do not roll back, surface it to the caller.
        $persisted = true;

        try {
            $this->makeClient($secret)->executeRaw(['CONFIG', 'REWRITE']);
        } catch (\Throwable) {
            $persisted = false;
        }

        // 5. Make this process's framework connection use the new password so the
        //    command's queue:restart / horizon:terminate can still reach Redis.
        $this->refreshFrameworkConnections($secret);

        if ($wasCached) {
            $this->rebuildConfigCache();
        }

        $verified = $this->canAuthenticateWith($secret);

        return [
            'action' => $verified ? 'secured' : 'applied_unverified',
            'changed' => true,
            'persisted' => $persisted,
            'secret' => $secret,
        ];
    }

    /**
     * The effective password from .env, or null when unset/null/empty.
     */
    public function currentEnvPassword(): ?string
    {
        $raw = $this->readRawEnvValue();

        if ($raw === null) {
            return null;
        }

        $value = trim($raw, " \t\"'");

        if (in_array(strtolower($value), self::EMPTY_VALUES, true)) {
            return null;
        }

        return $value;
    }

    /**
     * Build a Redis client, optionally authenticated. Isolated so tests can
     * supply a fake and so the live Redis is never touched by the unit suite.
     */
    protected function makeClient(?string $password): Client
    {
        if ($this->clientFactory !== null) {
            return ($this->clientFactory)($password);
        }

        $config = config('database.redis.default');

        $parameters = [
            'scheme' => 'tcp',
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => (int) ($config['port'] ?? 6379),
            'timeout' => 2.0,
        ];

        if ($password !== null && $password !== '') {
            $parameters['password'] = $password;
        }

        return new Client($parameters);
    }

    /**
     * Read the raw value token of REDIS_PASSWORD from .env, or null if absent.
     */
    private function readRawEnvValue(): ?string
    {
        $content = @file_get_contents($this->envPath);

        if ($content === false) {
            return null;
        }

        $pair = (new EnvironmentSetCommand)->readKeyValuePair($content, self::ENV_KEY);

        if ($pair === null) {
            return null;
        }

        return explode('=', $pair, 2)[1] ?? '';
    }

    /**
     * Write REDIS_PASSWORD into .env idempotently, reusing env:set's logic.
     */
    private function writeEnvPassword(string $value): void
    {
        $this->persistEnvValue($value);
    }

    /**
     * Restore REDIS_PASSWORD to a previous raw token (or 'null' when it was
     * absent) during rollback.
     */
    private function restoreRawEnvValue(?string $previousRaw): void
    {
        $this->persistEnvValue($previousRaw === null ? 'null' : trim($previousRaw, " \t\"'"));
    }

    /**
     * Shared .env writer used by both the write and the rollback paths.
     */
    private function persistEnvValue(string $value): void
    {
        $content = file_get_contents($this->envPath);
        [$updated] = (new EnvironmentSetCommand)->setEnvVariable($content, self::ENV_KEY, $value);

        file_put_contents($this->envPath, $updated, LOCK_EX);
    }

    /**
     * Rebuild the config cache and hand it back to the web user. Run as root,
     * config:clear + config:cache leaves a root-owned cache file that the web
     * user cannot replace later. A no-op chown when not run as root.
     */
    private function rebuildConfigCache(): void
    {
        Artisan::call('config:cache');
        custom_chown(App::getCachedConfigPath());
    }

    /**
     * Point this process at the new password. Every named connection is updated,
     * including the `horizon` one Horizon copies from `default` at boot.
     * RedisManager copies the redis config when it is built, so updating config()
     * and purging connections is not enough: the manager would reconnect with the
     * old credential. Rebuild it, and drop the Redis-backed cache stores that
     * hold the old manager.
     */
    private function refreshFrameworkConnections(string $secret): void
    {
        $connections = collect(config('database.redis', []))
            ->except(['client', 'options', 'clusters'])
            ->filter(fn (mixed $connection): bool => is_array($connection))
            ->keys();

        foreach ($connections as $name) {
            config(["database.redis.{$name}.password" => $secret]);
            app('redis')->purge($name);
        }

        app()->forgetInstance('redis');
        Redis::clearResolvedInstance('redis');

        $redisStores = collect(config('cache.stores', []))
            ->filter(fn (mixed $store): bool => is_array($store) && ($store['driver'] ?? null) === 'redis')
            ->keys()
            ->all();

        app('cache')->forgetDriver($redisStores);
        app()->forgetInstance('cache.store');
    }

    /**
     * Pull the value out of a `CONFIG GET` reply, tolerating both the flat
     * [key, value] array predis returns and an empty result.
     *
     * @param  mixed  $reply
     */
    private function configValue($reply): string
    {
        if (is_array($reply) && array_key_exists(1, $reply)) {
            return (string) $reply[1];
        }

        return '';
    }

    /**
     * Human readable one liner for the status detail.
     */
    private function describe(bool $reachable, ?bool $passwordRequired): string
    {
        if (! $reachable) {
            return 'Redis is unreachable; authentication state could not be determined.';
        }

        if ($passwordRequired === true) {
            return 'Redis requires authentication.';
        }

        return 'Redis has no password set. Run: php artisan rconfig:redis-secure';
    }
}
