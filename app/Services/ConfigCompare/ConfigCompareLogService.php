<?php

namespace App\Services\ConfigCompare;

use App\Jobs\ConfigChangedNotificationJob;
use App\Models\Command;
use App\Models\Config;

class ConfigCompareLogService
{
    public function __construct(private Config $model, private ?Command $command = null) {}

    /**
     * Log a detected config change and dispatch the email notification job.
     * Recipients are resolved from per-user notification preferences, so no
     * email is sent unless a user has opted into config change alerts.
     */
    public function logAndNotify(string $diff): void
    {
        $msg = 'Config changed for device: ' . $this->model->device_name . ' command: ' . $this->model->command;

        activityLogIt(
            __CLASS__,
            __FUNCTION__,
            'warn',
            $msg,
            'config',
            $this->model->device_name,
            $this->model->device_id,
            $this->model->device_category,
            $this->model->id
        );

        ConfigChangedNotificationJob::dispatch($this->model);
    }
}
