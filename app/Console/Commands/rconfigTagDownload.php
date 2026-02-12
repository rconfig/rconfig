<?php

namespace App\Console\Commands;

use App\CustomClasses\DeviceDownloadClass;
use App\CustomClasses\FilterArgsForCommnds;
use App\CustomClasses\GetAndCheckTagIds;
use Illuminate\Console\Command;

class rconfigTagDownload extends Command
{
    protected $signature = 'rconfig:download-tag
                            {tagid*}
                            {--d|debug : rConfig debugging output}';

    protected $eventtype = 'rconfig:download-tag';

    protected $description = 'Download configurations for devices with one or multiple tags';

    public function handle()
    {
        // get ids from command
        $ids = (new FilterArgsForCommnds)->sanitize($this->argument('tagid'));
        $debug = $this->option('debug');

        $this->info('Start ' . $this->eventtype . ' IDs:' . implode(' ', $ids));

        $logmsg = 'CLI Download Started for ' . ($this->eventtype . 'IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        // get the records from the DB
        $tagrecords = (new GetAndCheckTagIds($ids))->GetTagRecords();

        // error if none returned
        if (count($tagrecords) === 0) {
            $this->info('No tag records returned. Download will now terminate!');

            return;
        }

        foreach ($tagrecords as $tagrecord) {
            // error if none returned
            if (count($tagrecord->device) === 0) {
                $this->error('No devices returned for this category with ID: ' . $tagrecord->id . '. Downloader will try next category in the list, or terminate!');

                continue;
            }

            // run the DeviceDownloadClass for all devices, and print console output to this classes console output
            $output = (new DeviceDownloadClass($tagrecord->device, $this->eventtype, $debug))->downloadDevices();
            foreach ($output as $output_type => $values) {
                // $key == info or error
                foreach ($values as $key1 => $value1) {
                    $this->$output_type($value1);
                }
            }

            if (!isDocker()) {
                if (app()->runningInConsole()) {
                    custom_chown(storage_path());
                }
            }
        }

        $logmsg = 'CLI Download ended for ' . ($this->eventtype . ' IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        $this->info('End ' . $this->eventtype . '');
    }
}
