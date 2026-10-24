<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RconfigWebUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:web-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print the OS user Horizon and the scheduler should run as on this host';

    /**
     * Prints rconfig_web_user() and nothing else, so setup_supervisor.sh can
     * read it and the shell side never keeps its own copy of the detection
     * logic. It does not check the user exists: the script validates it with
     * getent before writing any Supervisor config.
     */
    public function handle(): int
    {
        [$user] = rconfig_web_user();

        $this->line($user);

        return self::SUCCESS;
    }
}
