<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DBTaskCompleteNotification extends Notification
{
    use Queueable;

    protected $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'rConfig Download-Task Complete!',
            'description' => 'Scheduled task '.$this->report_data->task->id.' completed',
            'category' => 'task',
            'severity' => 'info',
            'icon' => 'pficon-info',
        ];
    }
}
