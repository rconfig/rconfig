<?php

namespace App\Console\Commands;

use App\Models\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class purgeFailedConfigs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:purge-failedconfigs
                            {deviceid?*}
                            {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge failed configs for some or all devices';

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
        $deviceids = $this->argument('deviceid');
        $all = $this->option('all');

        if (empty($deviceids) && $all === false) {
            $this->error('You must enter device IDs, or use the --all switch.');
            exit();
        }

        if (empty($deviceids) && $all === true) {
            $configs = Config::where('download_status', '!=', 1)->orWhereNull('download_status')->get();
        } else {
            $configs = Config::whereIn('device_id', $deviceids)->where('download_status', '!=', 1)->orWhereNull('download_status')->get();
        }

        if (count($configs) === 0) {
            $this->info('Nothing to purge! Thanks!');
        }

        foreach ($configs as $config) {
            if ($config->config_location != null) {
                File::delete($config->config_location);
            }
            Config::destroy($config->id);
        }

        $logmsg = count($configs) . ' invalid or failed configurations purged!';
        $this->info($logmsg);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'devices', '', '', 'config', $deviceids);

        return true;
    }
}
