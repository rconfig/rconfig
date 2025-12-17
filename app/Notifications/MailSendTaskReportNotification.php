<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MailSendTaskReportNotification extends Notification
{
    use Queueable;

    protected $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->report_data->task->task_name . ' - rConfig Download-Task Complete')
            ->markdown('emails.notifications.task.complete',
                ['greeting' => 'rConfig Download-Task Complete',
                    'task_id' => $this->report_data->task->id,
                    'task_name' => $this->report_data->task->task_name,
                    'task_desc' => $this->report_data->task->task_desc,
                    'start_time' => $this->report_data->start_time,
                    'seconds' => $this->report_data->end_time->diffInSeconds($this->report_data->start_time),
                    'url' => url('/scheduled-tasks'),
                ])
            ->attach($this->report_data->report_path);
    }
}
