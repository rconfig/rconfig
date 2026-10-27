<?php

namespace App\Console\Commands;

use App\Services\System\SelfSignedSslService;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class V8CoreInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v8core:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install rConfig v8 Core with beautiful CLI output';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Blue themed ASCII Art Logo - Clean without V8 CORE text
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════════════════════════╗');
        $this->line('║                                                                                  ║');
        $this->line('║                <fg=cyan>Network Configuration Management System</>                           ║');
        $this->line('║                   <fg=white>rConfig V8 Core • Open Source</>                               ║');
        $this->line('║                                                                                  ║');
        $this->line('╚══════════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Step 1: Package discovery
        $this->components->task('Discovering packages', function () {
            sleep(1);

            return true;
        });

        // Step 2: Generate key
        $this->components->task('Generating application key', function () {
            Artisan::call('key:generate', [], $this->output);

            return true;
        });

        // Step 3: Run migrations
        $this->components->task('Running database migrations', function () {
            Artisan::call('migrate', ['--force' => true], $this->output);

            return true;
        });

        // Step 5: Clear cache
        $this->components->task('Clearing application cache', function () {
            Artisan::call('rconfig:clear-all', [], $this->output);

            return true;
        });

        // Step 6: Sync tasks
        $this->components->task('Synchronizing scheduled tasks', function () {
            Artisan::call('rconfig:sync-tasks', [], $this->output);

            return true;
        });

        // Step 7: Optimize
        $this->components->task('Optimizing application', function () {
            Artisan::call('optimize', [], $this->output);

            return true;
        });

        // Step 8: Secure Redis. Generates a password once and requires it on the
        // live instance so a fresh box ships closed. Idempotent: re-running the
        // installer detects the existing password and does not regenerate. Never
        // fatal to the install, because Redis may not be running yet on a
        // bare-metal box; the dashboard health check nags until it is secured.
        $this->secureRedis();

        $this->newLine();
        $this->components->info('✓ rConfig v8 Core installed successfully!');
        $this->newLine();

        // The scheduler runs from /etc/cron.d as the web user, never from
        // root's crontab. Docker runs it from the container's Supervisor.
        $cronInstalled = true;

        if (app(HostFilesystem::class)->isDocker()) {
            $this->components->info('Running in Docker: the container runs the scheduler itself, no cron file is needed.');
        } elseif ($this->components->confirm('Install the scheduler cron file (/etc/cron.d/rconfig-core)?', true)) {
            $cronInstalled = $this->setupCron();
        }

        $this->newLine();

        $starRepo = $this->components->confirm(
            'All done! Would you like to show some love by starring the rConfig v8 Core repo on GitHub?',
            true
        );

        if ($starRepo) {
            $this->openGitHub();
        }

        $this->newLine();
        $this->displayNextSteps();

        // Last, so a failure is the final thing the operator sees.
        $workerUserOk = app(HostFilesystem::class)->isDocker() || $this->checkWorkerUser();

        return $cronInstalled && $workerUserOk ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Runs rconfig:check-worker-user and, if it fails, says that rConfig is
     * installed but something still runs as root, and how to fix it.
     */
    protected function checkWorkerUser(): bool
    {
        $this->components->info('Checking that nothing runs as root');

        $exitCode = Artisan::call('rconfig:check-worker-user', [], $this->output);

        $this->newLine();

        if ($exitCode === Command::SUCCESS) {
            return true;
        }

        $this->components->error('rConfig is installed, but the worker user check failed (see the FAIL lines above).');
        $this->line('   Fix: run <fg=cyan>sudo ./update.sh --no-pull</> from ' . base_path());
        $this->line('   Then check again with: <fg=cyan>php artisan rconfig:check-worker-user</>');
        $this->newLine();

        return false;
    }

    /**
     * Generate and apply a Redis password at install time so a fresh install is
     * never left with an unauthenticated queue. Idempotent and non-fatal.
     */
    protected function secureRedis(): void
    {
        $this->components->task('Securing Redis', function () {
            // --no-restart: consumers are not running yet during install, and a
            // horizon:terminate here would be a no-op at best.
            $exitCode = Artisan::call('rconfig:redis-secure', ['--no-restart' => true]);

            if ($exitCode !== Command::SUCCESS) {
                $this->components->warn(
                    'Could not secure Redis automatically (is Redis running?). '
                    . 'Run: php artisan rconfig:redis-secure once Redis is available.'
                );
            }

            return true;
        });
    }

    /**
     * Installs the scheduler cron file through rconfig:setup-cron, which runs
     * setup_cron.sh as root or prints the sudo command otherwise.
     */
    protected function setupCron(): bool
    {
        $exitCode = Artisan::call('rconfig:setup-cron', [], $this->output);

        $this->newLine();

        return $exitCode === Command::SUCCESS;
    }

    protected function openGitHub()
    {
        $url = 'https://github.com/rconfig/rconfig';

        $this->newLine();
        $this->components->info('⭐ Show your support by starring our repo!');
        $this->line("   <fg=cyan>$url</>");
        $this->newLine();
        $this->line('   <fg=yellow>→ Copy the link above and open it in your browser</>');
        $this->newLine();
    }

    protected function displayNextSteps()
    {
        $this->line('╔════════════════════════════════════════════════════════════════════════════════════════╗');
        $this->line('║                              <fg=bright-blue>Next Steps</>                                      ║');
        $this->line('╚════════════════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->components->twoColumnDetail('📧 Default Login', 'admin@domain.com');
        $this->components->twoColumnDetail('🔑 Default Password', 'admin');
        $this->components->twoColumnDetail('📚 Documentation', 'https://v8coredocs.rconfig.com');
        $this->components->twoColumnDetail('💬 Support', 'https://github.com/rconfig/rconfig/issues');
        $this->components->twoColumnDetail('🌟 Star us on GitHub', 'https://github.com/rconfig/rconfig');

        $this->newLine();
        $this->components->warn('⚠️  Please change the default credentials immediately!');

        // A fresh install serves plain HTTP; Docker leaves TLS to the reverse proxy.
        if (! SelfSignedSslService::appUsesHttps() && ! app(HostFilesystem::class)->isDocker()) {
            $this->components->warn('🔒 rConfig is served over HTTP. Enable HTTPS with a self-signed certificate:');
            $this->line('   <fg=cyan>' . SelfSignedSslService::COMMAND . '</>');
            $this->line('   Or install your own certificate: ' . SelfSignedSslService::DOCS_URL);
        }

        $this->newLine();

        $this->line('<fg=blue>Thank you for choosing rConfig v8 Core! 🚀</>');
        $this->newLine();
    }
}
