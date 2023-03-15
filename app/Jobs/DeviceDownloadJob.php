<?php

namespace App\Jobs;

use App\CustomClasses\DeviceRecordPrepare;
use App\CustomClasses\SaveConfigsToDiskAndDb;
use App\CustomClasses\SetDeviceStatus;
use App\Http\Controllers\Connections\MainConnectionManager;
use App\Models\User;
use App\Notifications\DBDeviceConnectionFailureNotification;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class DeviceDownloadJob extends Command
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $output;

    protected $eventtype;

    protected $devicerecord;

    protected $debug;

    protected $report_id;

    protected $parent_function;

    protected $parent_class;

    public function __construct($devicerecord, $eventtype, $debug, $report_id = null)
    {
        $this->output = [];
        $this->eventtype = $eventtype;
        $this->devicerecord = $devicerecord;
        $this->debug = $debug;
        $this->report_id = $report_id;
        $this->parent_class = get_parent_class();
        $this->parent_function = debug_backtrace()[1]['function'];
    }

    public function handle()
    {
        // if ($this->batch()->cancelled()) {
        //     // Determine if the batch has been cancelled...
        //     return;
        // }

        // start the download
        $this->devicerecord['start_time'] = Carbon::now();

        $logmsg = 'Start device download for '.($this->devicerecord['device_name'].' ID:'.$this->devicerecord['id']);
        activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $this->devicerecord['device_name'], $this->devicerecord['id'], $this->eventtype, $this->devicerecord['id']);
        $this->output['info'][] = $logmsg;

        $this->devicerecord = (new DeviceRecordPrepare($this->devicerecord))->DeviceRecordToArray();
        $connectionObj = new MainConnectionManager($this->devicerecord, $this->debug);
        $configsArray = $connectionObj->setupConnectAndReturnOutput();

        // throw and error if configsArray is false
        if ($configsArray === false || isset($configsArray['failure'])) {
            $this->devicerecord['end_time'] = Carbon::now();
            $logmsg = 'No config data returned for '.($this->devicerecord['device_name'].' - ID:'.$this->devicerecord['id'].'. Check your logs for more information');
            Notification::send(User::all(), new DBDeviceConnectionFailureNotification($logmsg, $this->devicerecord['id']));

            $configSaveResult = (new SaveConfigsToDiskAndDb('device_download', null, 0, $this->devicerecord, $this->report_id))->saveConfigs();

            $this->output['error'][] = $logmsg;
            activityLogIt($this->parent_class, $this->parent_function, 'error', $logmsg, 'connection', $this->devicerecord['device_name'], $this->devicerecord['id'], $this->eventtype, $this->devicerecord['id']);

            (new SetDeviceStatus($this->devicerecord['id'], 0))->setDeviceStatus();

            return; // continue looping around if more devices
        }
        (new SetDeviceStatus($this->devicerecord['id'], 1))->setDeviceStatus();
        $logmsg = 'End device download for '.($this->devicerecord['device_name'].' ID:'.$this->devicerecord['id']);
        activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $this->devicerecord['device_name'], $this->devicerecord['id'], $this->eventtype, $this->devicerecord['id']);

        if (isset($configsArray['failure'])) {
            $this->output['error'][] = $configsArray['failure'];

            return;
        }

        foreach ($configsArray as $commandName => $configArray) {
            $this->devicerecord['end_time'] = Carbon::now();
            $configSaveResult = (new SaveConfigsToDiskAndDb('device_download', $commandName, $configArray, $this->devicerecord, $this->report_id))->saveConfigs();
            $configresultText = $configSaveResult['success'] === true ? ' was successful' : 'failed';
            $logmsg = 'Config downloaded for '.$this->devicerecord['device_name'].' with command: "'.$configSaveResult['commandName'].'"'.$configresultText;
            $this->output['info'][] = $logmsg;
            activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $this->devicerecord['device_name'], $this->devicerecord['id'], 'device');
        }
        Redis::set('download-now-'.$this->devicerecord['id'], 'false');

        return $this->output;
    }
}
