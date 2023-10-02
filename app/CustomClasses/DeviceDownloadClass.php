<?php

namespace App\CustomClasses;

use App\Http\Controllers\Connections\MainConnectionManager;
use App\Models\User;
use App\Notifications\DBDeviceConnectionFailureNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class DeviceDownloadClass extends Command
{
    protected $output;

    protected $eventtype;

    protected $devicerecords;

    protected $debug;

    protected $report_id;

    protected $parent_function;

    protected $parent_class;

    public function __construct($devicerecords, $eventtype, $debug, $report_id = null)
    {
        $this->output = [];
        $this->eventtype = $eventtype;
        $this->devicerecords = $devicerecords;
        $this->debug = $debug;
        $this->report_id = $report_id;
        $this->parent_class = get_parent_class();
        $this->parent_function = debug_backtrace()[1]['function'];
    }

    public function downloadDevices()
    {
        // start the download
        foreach ($this->devicerecords as $devicerecord) {
            $devicerecord['start_time'] = Carbon::now();

            $logmsg = 'Start device download for ' . ($devicerecord['device_name'] . ' ID:' . $devicerecord['id']);
            activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $devicerecord['device_name'], $devicerecord['id'], $this->eventtype, $devicerecord['id']);
            $this->output['info'][] = $logmsg;

            $devicerecord = (new DeviceRecordPrepare($devicerecord))->DeviceRecordToArray();
            $connectionObj = new MainConnectionManager($devicerecord, $this->debug);
            $configsArray = $connectionObj->setupConnectAndReturnOutput();

            // throw and error if configsArray is false
            if ($configsArray === false || isset($configsArray['failure'])) {
                $devicerecord['end_time'] = Carbon::now();
                $logmsg = 'No config data returned for ' . ($devicerecord['device_name'] . ' - ID:' . $devicerecord['id'] . '. Check your logs for more information');
                Notification::send(User::all(), new DBDeviceConnectionFailureNotification($logmsg, $devicerecord['id']));

                $configSaveResult = (new SaveConfigsToDiskAndDb('device_download', 'Failed config download', 0, $devicerecord, $this->report_id))->saveConfigs();

                $this->output['error'][] = $logmsg;
                activityLogIt($this->parent_class, $this->parent_function, 'error', $logmsg, 'connection', $devicerecord['device_name'], $devicerecord['id'], $this->eventtype, $devicerecord['id']);

                (new SetDeviceStatus($devicerecord['id'], 0))->setDeviceStatus();

                continue; // continue looping around if more devices
            }
            (new SetDeviceStatus($devicerecord['id'], 1))->setDeviceStatus();
            $logmsg = 'End device download for ' . ($devicerecord['device_name'] . ' ID:' . $devicerecord['id']);
            activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $devicerecord['device_name'], $devicerecord['id'], $this->eventtype, $devicerecord['id']);

            if (isset($configsArray['failure'])) {
                $this->output['error'][] = $configsArray['failure'];

                continue;
            }

            foreach ($configsArray as $commandName => $configArray) {
                $devicerecord['end_time'] = Carbon::now();
                $configSaveResult = (new SaveConfigsToDiskAndDb('device_download', $commandName, $configArray, $devicerecord, $this->report_id))->saveConfigs();
                $configresultText = $configSaveResult['success'] === true ? ' was successful' : ' failed';
                $logmsg = 'Config downloaded for ' . $devicerecord['device_name'] . ' with command: "' . $configSaveResult['commandName'] . '"' . $configresultText;
                $this->output['info'][] = $logmsg;
                activityLogIt($this->parent_class, $this->parent_function, 'info', $logmsg, 'connection', $devicerecord['device_name'], $devicerecord['id'], 'device');
            }

            // dd($configCheckResult);
        }
        if (isset($devicerecord)) {
            Redis::set('download-now-' . $devicerecord['id'], 'false');
        }

        return $this->output;
    }
}
