<?php

namespace App\Console\Commands;

use App\CustomClasses\DeviceDownloadClass;
use App\CustomClasses\FilterArgsForCommnds;
use App\CustomClasses\GetAndCheckDeviceIds;
use Illuminate\Console\Command;

class rconfigDeviceDownload extends Command
{
    protected $signature = 'rconfig:download-device
                            {deviceid*}
                            {--d|debug : rConfig debugging output}';

    protected $eventtype = 'rconfig:download-device';

    protected $description = 'Download configurations for one or multiple devices via the CLI';
    protected $devicerecords;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // get ids from command
        $ids = (new FilterArgsForCommnds)->sanitize($this->argument('deviceid'));
        $debug = $this->option('debug');

        $this->info('Start ' . $this->eventtype . ' IDs:' . implode(' ', $ids));

        $logmsg = 'CLI Download Started for ' . ($this->eventtype . 'IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        // get the records from the DB
        $this->devicerecords = (new GetAndCheckDeviceIds($ids))->GetDeviceRecords();

        // error if none returned
        if (count($this->devicerecords) === 0) {
            $this->info('No device records returned. Download will now terminate!');

            return;
        }
        // run the DeviceDownloadClass for all devices, and print output to this classes output
        $output = (new DeviceDownloadClass($this->devicerecords, $this->eventtype, $debug))->downloadDevices();
        foreach ($output as $output_type => $values) {
            // $key == info or error
            foreach ($values as $key1 => $value1) {
                $this->$output_type($value1);
            }
        }

        $logmsg = 'CLI Download ended for ' . ($this->eventtype . ' IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        $this->info('End ' . $this->eventtype . '');

        if (!isDocker()) {
            if (app()->runningInConsole()) {
                custom_chown(storage_path());
            }
        }
    }
}
