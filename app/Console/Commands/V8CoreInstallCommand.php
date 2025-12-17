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
        $this->line('║   <fg=blue>██████╗  ██████╗ ██████╗ ███╗   ██╗███████╗██╗ ██████╗</>                         ║');
        $this->line('║   <fg=blue>██╔══██╗██╔════╝██╔═══██╗████╗  ██║██╔════╝██║██╔════╝</>                         ║');
        $this->line('║   <fg=blue>██████╔╝██║     ██║   ██║██╔██╗ ██║█████╗  ██║██║  ███╗</>                        ║');
        $this->line('║   <fg=blue>██╔══██╗██║     ██║   ██║██║╚██╗██║██╔══╝  ██║██║   ██║</>                        ║');
        $this->line('║   <fg=blue>██║  ██║╚██████╗╚██████╔╝██║ ╚████║██║     ██║╚██████╔╝</>                        ║');
        $this->line('║   <fg=blue>╚═╝  ╚═╝ ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚═╝     ╚═╝ ╚═════╝</>                         ║');
        $this->line('║                                                                                  ║');
        $this->line('║                <fg=cyan>Network Configuration Management System</>                           ║');
        $this->line('║                   <fg=white>Professional Grade • Open Source</>                               ║');
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

        // Step 4: Install Passport
        $this->components->task('Installing Laravel Passport', function () {
            Artisan::call('passport:install', [], $this->output);
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

    protected function setupCron()
    {
        $cronEntry = '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1';
        
        $this->components->info("Entry was added [$cronEntry]");
        $this->newLine();
        
        // Show the cron command to add
        $this->components->warn('Please add the following to your crontab if not added automatically:');
        $this->line($cronEntry);
    }

    protected function openGitHub()
    {
        $url = 'https://github.com/rconfig/rconfig';
        
        $this->components->info("Opening GitHub repository...");
        $this->line("URL: $url");
        
        // Try to open in browser
        if (PHP_OS_FAMILY === 'Windows') {
            @exec("start $url");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            @exec("open $url");
        } else {
            // Linux - may fail on some systems
            @exec("xdg-open $url 2>/dev/null &");
        }
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
