<?php

namespace App\Console\Commands;

use App\Services\Utilities\RedisSecurityService;
use Illuminate\Console\Command;

/**
 * Read-only canary for the Redis authentication state. Reports whether Redis
 * demands a password and, for context only, how it is bound. Drives the
 * dashboard health check and any upgrade tooling via its exit code.
 */
class RedisSecurityCheck extends Command
{
    /**
     * Exit code: Redis demands a password.
     */
    private const EXIT_SECURE = 0;

    /**
     * Exit code: Redis is reachable but demands no password.
     */
    private const EXIT_INSECURE = 1;

    /**
     * Exit code: Redis could not be reached, state undetermined.
     */
    private const EXIT_UNDETERMINED = 2;

    /**
     * @var string
     */
    protected $signature = 'rconfig:redis-security-check {--json : Emit the status as JSON}';

    /**
     * @var string
     */
    protected $description = 'Report whether Redis requires a password (the only real control on the queue)';

    public function handle(RedisSecurityService $service): int
    {
        $status = $service->status();

        if ($this->option('json')) {
            $this->line((string) json_encode($status, JSON_PRETTY_PRINT));
        } else {
            $this->renderTable($status);
        }

        if (! $status['reachable']) {
            return self::EXIT_UNDETERMINED;
        }

        return $status['password_required'] ? self::EXIT_SECURE : self::EXIT_INSECURE;
    }

    /**
     * @param  array{reachable: bool, password_required: bool|null, app_authenticated: bool, secure: bool, bind: string, protected_mode: string, detail: string}  $status
     */
    private function renderTable(array $status): void
    {
        $this->newLine();

        if (! $status['reachable']) {
            $this->components->error('Redis is unreachable; authentication state could not be determined.');
        } elseif ($status['secure']) {
            $this->components->info('Redis requires authentication.');
        } else {
            $this->components->error('Redis has no password set. Run: php artisan rconfig:redis-secure');
        }

        $this->components->twoColumnDetail('Password required', $this->flag($status['password_required']));
        $this->components->twoColumnDetail('App can authenticate', $this->flag($status['app_authenticated']));
        $this->components->twoColumnDetail('<fg=gray>Bind (context only)</>', '<fg=gray>' . $status['bind'] . '</>');
        $this->components->twoColumnDetail('<fg=gray>Protected mode (context only)</>', '<fg=gray>' . $status['protected_mode'] . '</>');

        $this->newLine();
        $this->line('<fg=gray>Bind and protected-mode are context only and never indicate a secure instance.</>');
        $this->newLine();
    }

    private function flag(?bool $value): string
    {
        return match ($value) {
            true => '<fg=green>yes</>',
            false => '<fg=red>no</>',
            default => '<fg=yellow>unknown</>',
        };
    }
}
