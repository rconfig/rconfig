<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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

    /**
     * Build the Download Task Report email.
     *
     * The subject differs from the Task Completed email so the two can be told apart.
     * A missing report file no longer aborts the send: the email still goes out without
     * the attachment, and the miss is logged, instead of the whole email being dropped.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->report_data->task->task_name . ' - rConfig Download-Task Report')
            ->markdown('emails.notifications.task.complete',
                ['greeting' => 'rConfig Download-Task Report',
                    'task_id' => $this->report_data->task->id,
                    'task_name' => $this->report_data->task->task_name,
                    'task_desc' => $this->report_data->task->task_desc,
                    'start_time' => $this->report_data->start_time,
                    'seconds' => $this->report_data->end_time->diffInSeconds($this->report_data->start_time),
                    'url' => url('/scheduled-tasks'),
                ]);

        if (is_readable($this->report_data->report_path)) {
            $message->attach($this->report_data->report_path);
        } else {
            Log::warning('Download Task Report email sent without its report: report file not found at ' . $this->report_data->report_path);
        }

        return $message;
    }
}
