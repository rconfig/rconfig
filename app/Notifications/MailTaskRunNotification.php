<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Log;

class MailTaskRunNotification extends Notification
{
    protected $task;

    protected $seconds;

    public function __construct($seconds, $task)
    {
        $this->task = $task;
        $this->seconds = $seconds;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Log::info('Sending task notification email');
        try {
            return (new MailMessage)
                ->subject('rConfig Scheduled Task Notification')
                ->markdown(
                    'emails.notifications.task.complete',
                    [
                        'greeting' => 'rConfig Scheduled Task Notification!',
                        'task_id' => $this->task['id'],
                        'task_name' => $this->task['task_name'],
                        'task_desc' => $this->task['task_desc'],
                        'start_time' => Carbon::now()->format('M d, Y G:iA'),
                        'seconds' => $this->seconds,
                        'url' => url('/scheduled-tasks'),
                    ]
                );
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'task_notification');
        }
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
