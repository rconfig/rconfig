<?php

namespace App\Console\Commands;

use App\Services\Utilities\RedisSecurityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Self-remediation: generate a Redis password, write it to .env, require it on
 * the live instance, persist it, and restart the queue consumers so predis
 * reconnects with the new credential. Fails closed, idempotent, re-runnable.
 */
class RedisSecure extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rconfig:redis-secure
        {--force : Regenerate even when REDIS_PASSWORD is already set in .env}
        {--dry-run : Report what would change without altering anything}
        {--no-restart : Apply the password but do not restart queue consumers}';

    /**
     * @var string
     */
    protected $description = 'Secure Redis by setting a password and requiring it on the live instance';

    public function handle(RedisSecurityService $service): int
    {
        $force = (bool) $this->option('force');

        if ($this->option('dry-run')) {
            return $this->reportDryRun($service, $force);
        }

        try {
            $result = $service->secure($force);
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($result['action'] === 'already_secured') {
            $this->components->info('Redis is already secured and .env is in sync. Nothing to do.');

            return self::SUCCESS;
        }

        $this->components->info('Generated a new Redis password and required it on the live instance.');

        if (! $result['persisted']) {
            $this->components->warn(
                'CONFIG REWRITE failed: the password is live but not persisted to the Redis config file. '
                . 'It will not survive a Redis restart until persisted (check that Redis was started with a config file).'
            );
        }

        if ($this->option('no-restart')) {
            $this->components->warn('Skipping consumer restart (--no-restart). Restart Horizon/queue workers so they reconnect with the new password.');
        } else {
            $this->restartConsumers();
        }

        if ($result['action'] !== 'secured') {
            $this->components->warn('The new password could not be verified against the live instance. Run rconfig:redis-security-check to confirm.');

            return self::FAILURE;
        }

        $this->components->info('Redis is now secured. Verify with: php artisan rconfig:redis-security-check');

        return self::SUCCESS;
    }

    /**
     * Restart the queue consumers so their predis connections re-open and
     * authenticate. queue:restart signals plain workers; horizon:terminate makes
     * the Horizon master exit so supervisor (autorestart) relaunches it with the
     * refreshed config. Both supervisor setups (bare metal and the container)
     * autorestart these programs, so no sudo supervisorctl call is needed.
     */
    private function restartConsumers(): void
    {
        try {
            Artisan::call('queue:restart');
            $this->components->info('Broadcast queue restart signal.');
        } catch (Throwable $e) {
            $this->components->warn('Could not broadcast the queue restart signal: ' . $e->getMessage());
        }

        try {
            Artisan::call('horizon:terminate');
            $this->components->info('Signalled Horizon to restart; supervisor will relaunch it with the new password.');
        } catch (Throwable $e) {
            $this->components->warn('Could not signal Horizon to terminate: ' . $e->getMessage());
        }
    }

    private function reportDryRun(RedisSecurityService $service, bool $force): int
    {
        $current = $service->currentEnvPassword();

        if ($current !== null && ! $force) {
            if ($service->canAuthenticateWith($current)) {
                $this->components->info('[dry run] Redis is already secured and in sync. Nothing would change.');
            } else {
                $this->components->warn('[dry run] REDIS_PASSWORD is set but the live Redis does not accept it. Would abort without --force.');
            }

            return self::SUCCESS;
        }

        $this->components->info('[dry run] Would generate a new password, write REDIS_PASSWORD to .env, rebuild config cache, CONFIG SET requirepass, CONFIG REWRITE, and restart consumers.');

        return self::SUCCESS;
    }
}
