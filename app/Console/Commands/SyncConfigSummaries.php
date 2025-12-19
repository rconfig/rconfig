<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\ConfigSummary;
use App\Models\Device;
use Illuminate\Console\Command;

class SyncConfigSummaries extends Command
{
    protected $signature = 'rconfig:config-summaries-sync';
    protected $description = 'Sync Config Summaries';

    public function handle()
    {
        $this->syncSummaries();
    }

    public function syncSummaries()
    {
        $this->info("\nConfig count sync started!");
        ConfigSummary::truncate();

        $devices = Device::all();
        $totalDevices = count($devices);

        $progressBar = ! app()->runningInConsole() || app()->runningUnitTests() ? null : $this->output->createProgressBar($totalDevices);

        foreach ($devices as $device) {
            if ($progressBar) {
                $progressBar->display();
            }
            $download_status_0_count = Config::where('device_id', $device->id)->where('download_status', 0)->count();
            $download_status_1_count = Config::where('device_id', $device->id)->where('download_status', 1)->count();
            $download_status_2_count = Config::where('device_id', $device->id)->where('download_status', 2)->count();
            $total_count = $download_status_0_count + $download_status_1_count + $download_status_2_count;
            $total_file_count = Config::where('device_id', $device->id)->count();

            ConfigSummary::insert(
                [
                    'device_id' => $device->id,
                    'download_status_0_count' => $download_status_0_count,
                    'download_status_1_count' => $download_status_1_count,
                    'download_status_2_count' => $download_status_2_count,
                    'total_file_count' => $total_file_count,
                    'total_count' => $total_count,
                ]
            );
            $summary = ConfigSummary::where('device_id', $device->id)->first();

            if ($progressBar) {
                $progressBar->advance();
            }
        }

        if ($progressBar) {
            $progressBar->finish();
        }

        $this->info("\nConfig count sync completed!");

        return 0;
    }
}
