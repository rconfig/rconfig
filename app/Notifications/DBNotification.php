<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DBNotification extends Notification
{
    use Queueable;

    protected $title;

    protected $description;

    protected $category;

    protected $severity;

    protected $icon;

    public function __construct($title, $description, $category, $severity, $icon)
    {
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->severity = $severity;
        $this->icon = $icon;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'severity' => $this->severity,
            'icon' => $this->icon,
        ];

        // log_name = severity level
        //  critical - pficon-error-circle-o
        //  error - pficon-error-circle-o
        //  warn - pficon-warning-triangle-o
        //  info - pficon-info
        //  default - pficon-info

        // category
        //  scheduler
        //  downloader
        //  connection
        //  config
        // task
    }
}
