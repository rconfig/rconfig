<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\MailSendTaskReportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendTaskReportNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function handle()
    {
        try {
            Notification::send(User::allUsersAndRecipients(), new MailSendTaskReportNotification($this->report_data));
        } catch (\Exception $e) {
            $response = $e->getMessage();
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $response, 'config');
        }
    }
}
