<?php

namespace App\Notifications;

use App\Services\Notifications\Shared\HealthCheckAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MailHealthCheckNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly HealthCheckAlert $alert) {}

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
            $mail = new MailMessage;

            if ($this->alert->recovered) {
                $mail->subject('rConfig ' . $this->alert->title())
                    ->greeting($this->alert->title())
                    ->line('These ' . $this->alert->checksNoun() . ' on ' . config('app.name') . ' are passing again:');
            } else {
                $mail->error()
                    ->subject('rConfig ' . $this->alert->title())
                    ->greeting($this->alert->title())
                    ->line('These ' . $this->alert->checksNoun() . ' on ' . config('app.name') . ' have started failing:');
            }

            foreach ($this->alert->checkLines() as $line) {
                $mail->line('• ' . $line);
            }

            return $mail->action($this->alert->panelLabel(), $this->alert->panelUrl());
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'system');

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
