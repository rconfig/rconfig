<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\Config;
use App\Notifications\MailConfigChangedNotification;
use App\Traits\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConfigChangedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotificationDispatcher, Queueable, SerializesModels;

    public function __construct(protected Config $model) {}

    public function handle(): void
    {
        // Email notification only: sent to users who opted into config change alerts via mail.
        $this->sendToSingleChannel(
            NotificationType::CONFIG_CHANGED,
            NotificationChannel::MAIL,
            new MailConfigChangedNotification($this->model)
        );
    }
}
