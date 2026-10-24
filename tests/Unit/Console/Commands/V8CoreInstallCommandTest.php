<?php

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * The installer's other steps write .env, migrate, restart Horizon, rewrite
 * the Redis config and install the cron file, so each one is replaced with a
 * fake command that only records it ran. rconfig:check-worker-user stays real
 * and reads the faked HostFilesystem.
 */
beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
    Process::preventStrayProcesses();
    Process::fake();

    $this->ran = new ArrayObject;

    foreach (['key:generate', 'migrate', 'rconfig:clear-all', 'rconfig:sync-tasks', 'optimize', 'rconfig:redis-secure', 'rconfig:setup-cron'] as $name) {
        $command = new class($name, $this->ran) extends Command
        {
            public function __construct(string $name, private ArrayObject $ran)
            {
                $this->signature = $name;

                parent::__construct();

                $this->ignoreValidationErrors();
            }

            public function handle(): int
            {
                $this->ran[] = $this->getName();

                return self::SUCCESS;
            }
        };

        app(Kernel::class)->registerCommand($command);
    }
});

test('the installer runs the worker user check last and succeeds when it passes', function () {
    $this->fakeHealthyWorkerBox(isRoot: true);

    $this->artisan('v8core:install')
        ->expectsConfirmation('Install the scheduler cron file (/etc/cron.d/rconfig-core)?', 'yes')
        ->expectsConfirmation('All done! Would you like to show some love by starring the rConfig v8 Core repo on GitHub?', 'no')
        ->expectsOutputToContain('Checking that nothing runs as root')
        ->expectsOutputToContain('Overall: All good')
        ->doesntExpectOutputToContain('the worker user check failed')
        ->assertExitCode(0);

    expect($this->ran->getArrayCopy())->toContain('rconfig:setup-cron');
});

test('the installer prints the check output and the fix, and fails, when the check fails', function () {
    $host = $this->fakeHealthyWorkerBox(isRoot: true);
    $host->processList[0]['uid'] = 0;

    $this->artisan('v8core:install')
        ->expectsConfirmation('Install the scheduler cron file (/etc/cron.d/rconfig-core)?', 'yes')
        ->expectsConfirmation('All done! Would you like to show some love by starring the rConfig v8 Core repo on GitHub?', 'no')
        ->expectsOutputToContain('FAIL Horizon: Horizon runs as root (PID 4321)')
        ->expectsOutputToContain('Overall: Mixed')
        ->expectsOutputToContain('rConfig is installed, but the worker user check failed')
        ->expectsOutputToContain('sudo ./update.sh --no-pull')
        ->assertExitCode(1);
});

test('the installer does not run the worker user check in Docker', function () {
    $host = $this->fakeHealthyWorkerBox(isRoot: true, isDocker: true);
    $host->processList[0]['uid'] = 0;

    $this->artisan('v8core:install')
        ->expectsConfirmation('All done! Would you like to show some love by starring the rConfig v8 Core repo on GitHub?', 'no')
        ->doesntExpectOutputToContain('Checking that nothing runs as root')
        ->assertExitCode(0);

    expect($this->ran->getArrayCopy())->not->toContain('rconfig:setup-cron');
});
