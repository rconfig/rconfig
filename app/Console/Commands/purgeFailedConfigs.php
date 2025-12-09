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

    protected $description = 'Purge failed configs for some or all devices';

    public function handle()
    {
        $deviceids = $this->argument('deviceid');
        $all = $this->option('all');

        if (empty($deviceids) && $all === false) {
            $this->error('You must enter device IDs, or use the --all switch.');
            exit();
        }

        $deletedConfigCount = 0;
        if (empty($deviceids) && $all === true) {
            $ids = Config::where('download_status', 0)->pluck('id')->toArray();
            $progressBar = ! app()->runningInConsole() || app()->runningUnitTests() ? null : $this->output->createProgressBar(count($ids));

            foreach ($ids as $id) {
                if ($progressBar) {
                    $progressBar->display();
                }
                // files are deleted from the model
                Config::destroy($id);
                $deletedConfigCount += 1;
                if ($progressBar) {
                    $progressBar->advance();
                }
            }
        } else {
            $deviceids = array_map('intval', $deviceids);
            $ids = Config::whereIn('device_id', $deviceids)->where('download_status', 0)->pluck('id')->toArray();
            $progressBar = ! app()->runningInConsole() || app()->runningUnitTests() ? null : $this->output->createProgressBar(count($ids));

            foreach ($ids as $id) {
                if ($progressBar) {
                    $progressBar->display();
                }
                // files are deleted from the model
                Config::destroy($id);
                $deletedConfigCount += 1;
                if ($progressBar) {
                    $progressBar->advance();
                }
            }
        }

        if ($progressBar) {
            $progressBar->finish();
        }

        $logmsg = $deletedConfigCount . ' invalid or failed configurations purged!';
        $this->info($logmsg);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'devices', '', '', 'config', $deviceids);

        if (! app()->runningUnitTests()) {
            // theres an output issue with the progress bar in unit tests, so we don't queue this job in unit tests
            try {
                Artisan::queue('rconfig:config-summaries-sync')->onQueue('rConfigDefault');
            } catch (\Exception $e) {
                $this->error('Failed to sync config summaries: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
