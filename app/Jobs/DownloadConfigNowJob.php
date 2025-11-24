<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Notifications\DBNotification;
use App\Notifications\MailConfigDownloadNotification;
use App\Traits\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class DownloadConfigNowJob implements ShouldQueue
{
    use NotificationDispatcher, Queueable, SerializesModels;

    public $username;
    public $device_id;
    public $type;
    public $cause;

    public function __construct($device_id, $username = null, $type = 'device', $cause = 'system')
    {
        $this->username = $username;
        $this->device_id = $device_id;
        $this->type = $type;
        $this->cause = $cause;
    }

    public function handle()
    {
        Redis::set('download-now-' . $this->device_id, 'true');

        $download_command = 'rconfig:download-device ' . $this->device_id;

        $executionStartTime = microtime(true);

        Artisan::call($download_command);
        // get output
        $result = Artisan::output();

        // if $result contains 'No config data returned' then throw exception
        if (strpos($result, 'No config data returned') !== false) {
            $response = 'No config data returned for Device ID:' . $this->device_id;
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $response, 'config');
            throw new \Exception($response);
        }

        $executionEndTime = microtime(true);
        $seconds = round($executionEndTime - $executionStartTime, 2);
        $logmsg = 'Manual config download job completed for Device ID:' . $this->device_id;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'config');

        // Send DB notification for config download completion
        $this->sendToSingleChannel(
            NotificationType::CONFIG_DOWNLOAD_COMPLETED,
            NotificationChannel::DB,
            new DBNotification('Manual download completed', $logmsg, 'config', 'info', 'pficon-info')
        );

        // Send email notification for config download completion with error fallback
        $this->sendNotificationWithErrorFallback(
            NotificationType::CONFIG_DOWNLOAD_COMPLETED,
            new MailConfigDownloadNotification($this->device_id, $seconds, $this->username),
            [NotificationChannel::MAIL],
            new DBNotification('Notification error', 'Could not send config download email notification', 'system', 'error', 'pficon-error-circle-o')
        );
    }

    public function tags()
    {
        if ($this->cause === 'system') {
            return ['DownloadConfigNowJob:DeviceId:' . $this->device_id];
        }
        if ($this->cause === 'trap') {
            return ['DownloadConfigNowJob:Trap:DeviceId:' . $this->device_id];
        }

        return ['DownloadConfigNowJob:DeviceId:' . $this->device_id];
    }
}
