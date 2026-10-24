<?php

namespace App\Console\Commands;

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

        // Ask about cron
        $setupCron = $this->components->confirm(
            'Add a cron entry for task scheduling?',
            true
        );

        if ($setupCron) {
            $this->setupCron();
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

        return Command::SUCCESS;
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

    protected function setupCron()
    {
        $cronEntry = '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';

        $currentCron = shell_exec('crontab -l 2>/dev/null') ?? '';

        if (strpos($currentCron, 'artisan schedule:run') === false) {
            $newCron = trim($currentCron) . "\n" . $cronEntry . "\n";

            $tempFile = tempnam(sys_get_temp_dir(), 'crontab');
            file_put_contents($tempFile, $newCron);
            shell_exec("crontab $tempFile");
            unlink($tempFile);

            $this->components->success('Cron entry added successfully!');
        } else {
            $this->components->info('Cron entry already exists');
        }

        $this->newLine();

        $this->components->info("Cron entry: $cronEntry");
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
        $this->newLine();

        $this->line('<fg=blue>Thank you for choosing rConfig v8 Core! 🚀</>');
        $this->newLine();
    }
}
