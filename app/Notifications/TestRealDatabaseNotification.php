<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestRealDatabaseNotification extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // log_name = severity level
        //  critical - pficon-error-circle-o
        //  error - pficon-error-circle-o
        //  warn - pficon-warning-triangle-o
        //  info - pficon-info
        //  default - pficon-info

        // category
        // scheduler
        // downloader
        // connection
        // config

        return [
            'title' => $this->details['title'],
            'description' => $this->details['description'],
            'category' => $this->details['category'],
            'severity' => $this->details['severity'],
            'icon' => $this->details['icon'],
            'resolve_link' => $this->details['resolve_link'],
            'resolved' => $this->details['resolved'],
        ];
    }
}
