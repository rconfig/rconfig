<?php

namespace App\Console\Commands;

use App\Services\Utilities\HostFilesystem;
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
            $this->restartHorizon();
            $this->info('------  Horizon Restarted!  ------');

            custom_chown(rconfig_appdir_path());
        }

        $this->restoreDockerStoragePermissions();

        echo exec('composer dump-autoload') . PHP_EOL;
        $this->info(config('app.name') . ' application settings have been cleared!');

        if ($this->configPermissionsAreLoose()) {
            $this->newLine();
            $this->warn('Stored device configurations are readable by other local accounts on this host.');
            $this->warn('Run: php artisan rconfig:set-config-permissions');
        }
    }

    /**
     * Makes Horizon exit so Supervisor relaunches it, as the configured user,
     * with the fresh config. It never restarts Supervisor itself: that would
     * bounce every program on the box, including rConfig PRO's on a shared
     * host, and needs sudo the web user does not have.
     */
    public function restartHorizon(): void
    {
        Artisan::call('horizon:terminate');
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
     * Folders clear-all rebuilds, relative to the app folder.
     */
    private const DOCKER_REBUILT_PATHS = ['bootstrap/cache', 'storage/framework', 'storage/logs'];

    /**
     * Put storage back the way the Docker entrypoint leaves it.
     *
     * Inside the container this command usually runs as root through docker
     * compose exec, so the caches it just rebuilt are root owned and Apache,
     * running as www-data, cannot replace them. Hand those folders back to the
     * web user and reset the storage folder to the entrypoint's 0775. It used
     * to chmod storage 0777 to paper over the ownership problem, which let any
     * local account add, rename or delete entries directly under storage.
     *
     * Only the rebuilt folders are chowned, never the whole tree: the config
     * data directory can hold hundreds of thousands of files. Bare metal is
     * left alone. Skips silently when storage is absent.
     */
    public function restoreDockerStoragePermissions(): void
    {
        if (getenv('IS_DOCKER') !== 'true') {
            return;
        }

        $storagePath = rconfig_appdir_storage_path();

        if (! is_dir($storagePath)) {
            return;
        }

        app(HostFilesystem::class)->chmod($storagePath, 0775);
        custom_chown($storagePath);

        foreach (self::DOCKER_REBUILT_PATHS as $relativePath) {
            $path = rconfig_appdir_path() . '/' . $relativePath;

            if (is_dir($path)) {
                custom_chown($path, true);
            }
        }
    }
}
