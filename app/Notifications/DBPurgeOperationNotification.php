<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DBPurgeOperationNotification extends Notification
{
    use Queueable;

    protected $username;

    protected $msg;

    public function __construct($msg, $username)
    {
        $this->username = $username;
        $this->msg = $msg;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        //category => task, config
        //severity => info
        //icon => pficon-info
        return [
            'title' => 'Data Purge Notification',
            'description' => $this->msg,
            'category' => 'config',
            'severity' => 'info',
            'icon' => 'pficon-info',
        ];
    }
}
