<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DBDeviceConnectionFailureNotification extends Notification
{
    use Queueable;

    protected $msg;

    protected $deviceid;

    public function __construct($logmsg, $deviceid)
    {
        $this->msg = $logmsg;
        $this->deviceid = $deviceid;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Device Connection Error',
            'description' => $this->msg,
            'category' => 'downloader',
            'error' => 'error',
            'icon' => 'pficon-error-circle-o',
        ];
    }
}
