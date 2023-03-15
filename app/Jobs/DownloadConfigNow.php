<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailConfigDownloadNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;

class DownloadConfigNow implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 5;

    public $timeout = 120;

    protected $username;

    protected $device_id;

    public function __construct($device_id, $username = null)
    {
        $this->username = $username;
        $this->device_id = $device_id;
    }

    public function handle()
    {
        Redis::set('download-now-' . $this->device_id, 'true');

        $download_command = 'rconfig:download-device ' . $this->device_id;

        $executionStartTime = microtime(true);

        Artisan::call($download_command);
        $executionEndTime = microtime(true);
        $seconds = round($executionEndTime - $executionStartTime, 2);
        $logmsg = 'Manual config download job completed for Device ID:' . $this->device_id;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'config');

        try {
            Notification::send(User::all(), new DBNotification('Manual download completed', $logmsg, 'config', 'info', 'pficon-info'));
        } catch (\Exception $e) {
            $response = 'Could not send DBNotification: ' . $e->getMessage();
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $response, 'config');
        }

        try {
            Notification::send(User::allUsersAndRecipients(), new MailConfigDownloadNotification($this->device_id, $seconds, $this->username));
        } catch (\Exception $e) {
            $response = 'Could not send mail notification: ' . $e->getMessage();
            Notification::send(User::all(), new DBNotification('Notification error', $response, 'system', 'error', 'pficon-error-circle-o'));
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $response, 'config');
        }
    }
}
