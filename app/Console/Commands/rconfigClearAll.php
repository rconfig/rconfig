<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class rconfigClearAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:clear-all {--npm : Include NPM Clear out}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all cache and config elements for Laravel, NPM and other dependencies';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(' ------  Begin rConfig Laravel Clear Out!  ------');
        // echo exec('printf "hello\nworld\n"');
        // echo exec('echo -e "This is line 1\r\nThis is line 2"');
        Artisan::call('config:clear');
        $this->info('------  Config Cleared!  ------');
        Artisan::call('cache:clear');
        $this->info('------  Cache Cleared!  ------');
        Artisan::call('route:clear');
        $this->info('------  Routes Cleared!  ------');
        Artisan::call('view:clear');
        $this->info('------  Views Cleared!  ------');
        Artisan::call('config:cache');
        $this->info('------  Config Cached!  ------');
        Artisan::call('route:cache');
        $this->info('------  Routes Cached!  ------');
        Artisan::call('view:cache');
        $this->info('------  Views Cached!  ------');
        Artisan::call('optimize');
        $this->info('------  Optimized!  ------');
        Artisan::call('queue:restart');
        $this->info('------  Queues Restarted!  ------');
        Artisan::call('config:cache');
        $this->info('------  Config Cached!  ------');

        echo exec('sudo supervisorctl update') . PHP_EOL;
        echo exec('sudo supervisorctl reread') . PHP_EOL;
        echo exec('if [ -f /etc/redhat-release ]; then systemctl restart supervisord; fi;') . PHP_EOL;
        echo exec('if [ -f /etc/lsb-release ]; then systemctl restart supervisor; fi;') . PHP_EOL;
        echo exec('if [ -f /etc/redhat-release ]; then chown -R apache:apache $PWD; fi;') . PHP_EOL;
        echo exec('if [ -f /etc/lsb-release ]; then sudo chown -R www-data:www-data /var/www/html/rconfig; fi;') . PHP_EOL;
        echo exec('composer dump-autoload') . PHP_EOL;
        $this->info(config('app.name') . ' application settings have been cleared!');
    }
}
