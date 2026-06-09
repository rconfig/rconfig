<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MailDeviceConnectionFailureNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $msg, protected int|string $deviceid) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): ?MailMessage
    {
        try {
            return (new MailMessage)
                ->error()
                ->subject('Device connection failure notification')
                ->greeting('Device connection failure!')
                ->line(new HtmlString('rConfig failed to connect to or download a config for Device ID:<strong>' . $this->deviceid . '</strong>.'))
                ->line($this->msg)
                ->action('View device', url('/devices/' . $this->deviceid))
                ->line('Check the device logs for more information.');
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'connection');

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [];
    }
}
