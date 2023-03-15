<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MailPurgeOperationNotification extends Notification
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
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('rConfig Data Purge Notification')
                    ->greeting('rConfig Data Purge Notification!')
                    ->line('Hello '.$this->username.',')
                    ->line($this->msg.'.')
                    ->action('View logs', url('/settings-logs'))
                    ->line('Thank you for using rConfig!');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
