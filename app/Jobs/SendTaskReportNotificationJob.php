<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Notifications\DBTaskCompleteNotification;
use App\Notifications\MailSendTaskReportNotification;
use App\Traits\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendTaskReportNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotificationDispatcher, Queueable, SerializesModels;

    public $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function handle()
    {
        // Send the report email to users subscribed to Download Task Report via email
        $this->sendToSingleChannel(
            NotificationType::TASK_DOWNLOAD_REPORT,
            NotificationChannel::MAIL,
            new MailSendTaskReportNotification($this->report_data)
        );

        // Send DB notification to users subscribed to Download Task Report in-app
        $this->sendToSingleChannel(
            NotificationType::TASK_DOWNLOAD_REPORT,
            NotificationChannel::DB,
            new DBTaskCompleteNotification($this->report_data)
        );
    }
}
