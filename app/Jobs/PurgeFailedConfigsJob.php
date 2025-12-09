<?php

namespace App\Jobs;

use App\Models\User;
use App\Enums\NotificationType;
use App\Notifications\DBNotification;
use App\Traits\NotificationDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class PurgeFailedConfigsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotificationDispatcher, Queueable, SerializesModels;

    protected $device_id;

    public function __construct($device_id)
    {
        $this->queue = 'rConfigDefault';
        $this->device_id = $device_id;
    }

    public function handle()
    {
        $command = 'rconfig:purge-failedconfigs '.$this->device_id;

        Artisan::call($command);
        $logmsg = 'A purge job for invalid or failed configs was run for device: '.$this->device_id;

        $this->sendToDefaultChannels(
            NotificationType::CONFIG_PURGE_FAILED_COMPLETED,
            new DBNotification('Purge configs job completed!', $logmsg, 'system', 'info', 'pficon-info')
        );

        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'config');
    }
}
