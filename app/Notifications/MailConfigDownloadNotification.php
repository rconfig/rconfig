<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MailConfigDownloadNotification extends Notification
{
    use Queueable;

    protected $device_id;

    protected $username;

    protected $seconds;

    public function __construct($device_id, $seconds, $username)
    {
        $this->device_id = $device_id;
        $this->username = $username;
        $this->seconds = $seconds;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        try {
            return (new MailMessage)
                ->subject('Manual config download notification')
                ->greeting('Manual config download notification!')
                ->line('Hello ' . $this->username . ',')
                ->line(new HtmlString('Manual config download for Device ID:' . $this->device_id . ' was completed in <strong>' . $this->seconds . '</strong> seconds.'))
                ->action('View configs', url('/devices'))
                ->line('Thank you for using rConfig!');
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
