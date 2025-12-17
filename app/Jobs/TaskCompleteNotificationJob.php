<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Notifications\DBTaskCompleteNotification;
use App\Notifications\MailTaskCompleteNotification;
use App\Traits\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class TaskCompleteNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotificationDispatcher, Queueable, SerializesModels;

    protected $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function handle()
    {
        // Send email notification to users who want task completion notifications via email
        $this->sendToSingleChannel(
            NotificationType::TASK_COMPLETED,
            NotificationChannel::MAIL,
            new MailTaskCompleteNotification($this->report_data)
        );

        // Send DB notification to users who want task completion notifications in-app
        $this->sendToSingleChannel(
            NotificationType::TASK_COMPLETED,
            NotificationChannel::DB,
            new DBTaskCompleteNotification($this->report_data)
        );
    }
}
