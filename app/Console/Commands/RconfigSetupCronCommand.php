<?php

namespace App\Console\Commands;

use App\Services\Utilities\HostFilesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RconfigSetupCronCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:setup-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the scheduler cron file in /etc/cron.d as the web user (runs setup_cron.sh, needs root)';

    /**
     * Runs setup_cron.sh, the single implementation of the scheduler cron
     * install, so v8core:install can offer it. The script writes to /etc/cron.d
     * and rewrites crontabs, so it only runs as root. Any other user is told
     * the sudo command instead. In Docker the container's Supervisor runs the
     * scheduler, so there is nothing to install.
     */
    public function handle(HostFilesystem $host): int
    {
        if ($host->isDocker()) {
            $this->components->info('Running in Docker: the container runs the scheduler itself, no cron file is needed.');

            return self::SUCCESS;
        }

        if (! $host->isRoot()) {
            $this->components->warn('Installing the scheduler cron file needs root. Run this from ' . base_path() . ':');
            $this->line('   <fg=cyan>sudo ./setup_cron.sh</>');

            return self::SUCCESS;
        }

        $result = Process::path(base_path())
            ->timeout(120)
            ->run(['bash', base_path('setup_cron.sh')]);

        $this->output->write($result->output());
        $this->output->write($result->errorOutput());

        if ($result->failed()) {
            $this->components->error('The scheduler cron file was not installed. Fix the error above, then run: sudo ./setup_cron.sh');

            return self::FAILURE;
        }

        $this->components->success('Scheduler cron file installed in /etc/cron.d/rconfig-core.');

        return self::SUCCESS;
    }
}
