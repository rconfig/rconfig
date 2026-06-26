<?php

namespace App\Notifications;

use App\Models\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MailConfigChangedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Config $model) {}

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): ?MailMessage
    {
        try {
            return (new MailMessage)
                ->subject('Config change notification - ' . $this->model->device_name)
                ->greeting('Config change detected for ' . $this->model->device_name)
                ->line(new HtmlString('A configuration change was detected for device <strong>' . e($this->model->device_name) . '</strong>.'))
                ->line('Command: ' . $this->model->command)
                ->line('New version: ' . $this->model->config_version)
                ->action('View configuration', url('/configs/view/' . $this->model->id))
                ->line('Open the configuration to review the full diff against the previous version.')
                ->line('Thank you for using rConfig!');
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'config_change_notification');

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
