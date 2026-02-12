<?php

namespace App\Console\Commands;

use App\CustomClasses\DeviceDownloadClass;
use App\CustomClasses\FilterArgsForCommnds;
use App\CustomClasses\GetAndCheckCategoryIds;
use Illuminate\Console\Command;

class rconfigCatDownload extends Command
{
    protected $signature = 'rconfig:download-category
                            {catid*}
                            {--d|debug : rConfig debugging output}';

    protected $eventtype = 'rconfig:download-category';

    protected $description = 'Download configurations for one or multiple categories of devices';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // get ids from command
        $ids = (new FilterArgsForCommnds)->sanitize($this->argument('catid'));
        $debug = $this->option('debug');

        $this->info('Start ' . $this->eventtype . ' IDs:' . implode(' ', $ids));

        $logmsg = 'CLI Download Started for ' . ($this->eventtype . 'IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        // get the records from the DB
        $categoryrecords = (new GetAndCheckCategoryIds($ids))->GetCategoryRecords();

        // error if none returned
        if (count($categoryrecords) === 0) {
            $this->info('No category records returned. Download will now terminate!');

            return;
        }

        foreach ($categoryrecords as $categoryrecord) {
            // error if none returned
            if (count($categoryrecord->device) === 0) {
                $this->error('No devices returned for this category with ID: ' . $categoryrecord->id . '. Downloader will try next category in the list, or terminate!');

                continue;
            }

            // run the DeviceDownloadClass for all devices, and print output to this classes output
            $output = (new DeviceDownloadClass($categoryrecord->device, $this->eventtype, $debug))->downloadDevices();
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
