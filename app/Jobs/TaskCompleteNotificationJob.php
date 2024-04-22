<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DBTaskCompleteNotification;
use App\Notifications\MailTaskCompleteNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class TaskCompleteNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function handle()
    {
        Notification::send(User::allUsersAndRecipients(), new MailTaskCompleteNotification($this->report_data));
        Notification::send(User::allUsersAndRecipients(), new DBTaskCompleteNotification($this->report_data));
    }
}
