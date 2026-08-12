<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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

        if (! getenv('IS_DOCKER')) {
            echo exec('sudo supervisorctl update') . PHP_EOL;
            echo exec('sudo supervisorctl reread') . PHP_EOL;
            echo exec('if [ -f /etc/redhat-release ]; then systemctl restart supervisord; fi;') . PHP_EOL;
            echo exec('if [ -f /etc/lsb-release ]; then systemctl restart supervisor; fi;') . PHP_EOL;

            custom_chown(rconfig_appdir_path());
        }

        $this->applyDockerStoragePermissions();

        echo exec('composer dump-autoload') . PHP_EOL;
        $this->info(config('app.name') . ' application settings have been cleared!');

        if ($this->configPermissionsAreLoose()) {
            $this->newLine();
            $this->warn('Stored device configurations are readable by other local accounts on this host.');
            $this->warn('Run: php artisan rconfig:set-config-permissions');
        }
    }

    /**
     * Number of configuration files to inspect before giving up.
     */
    private const PERMISSION_SAMPLE_SIZE = 25;

    /**
     * Whether stored configurations still carry permissions that expose them to
     * other local accounts.
     *
     * This samples a handful of files rather than walking the tree. clear-all is
     * a fast command that gets run often, and the config data directory can hold
     * hundreds of thousands of files, so a full walk here would be far too
     * expensive. Remediation is the job of rconfig:set-config-permissions; this
     * only nags. Returns false on any error, since a warning is not worth
     * breaking a cache clear over.
     */
    public function configPermissionsAreLoose(): bool
    {
        $dataPath = config_data_path();

        if (! is_dir($dataPath)) {
            return false;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dataPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $inspected = 0;

            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                // Only stored configurations, so unrelated artefacts such as a
                // .gitignore do not raise a false alarm.
                if ($item->isLink() || ! $item->isFile() || ! in_array($item->getExtension(), ['txt', 'json'], true)) {
                    continue;
                }

                $mode = @fileperms($item->getPathname());

                if ($mode !== false && ($mode & 0007) !== 0) {
                    return true;
                }

                if (++$inspected >= self::PERMISSION_SAMPLE_SIZE) {
                    break;
                }
            }
        } catch (\Exception) {
            return false;
        }

        return false;
    }

    /**
     * Loosen storage permissions when running inside the Docker image.
     *
     * Uses the configured application storage path rather than a hard coded
     * one, and skips silently when the directory is absent, so it never emits
     * a warning on layouts where storage lives elsewhere.
     */
    public function applyDockerStoragePermissions(): void
    {
        if (getenv('IS_DOCKER') !== 'true') {
            return;
        }

        $storagePath = rconfig_appdir_storage_path();

        if (is_dir($storagePath)) {
            chmod($storagePath, 0777);
        }
    }
}
