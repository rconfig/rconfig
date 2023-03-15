<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailTaskRunNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class TaskDownloadRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;

    protected $executionStartTime;

    public function __construct($task)
    {
        $this->task = $task;
        $this->executionStartTime = microtime(true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call($this->task['task_command'] . ' ' . $this->task['id']);
        $result = Artisan::output();
        $arr = explode("\n", $result);
        \Log::info(print_r($arr, true));

        $this->_notifyTaskSend($this->executionStartTime);
        $logmsg = 'Task command "' . $this->task['task_command'] . ' ' . $this->task['id'] . '" was run with ID:' . $this->task['id'];
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
    }

    private function _notifyTaskSend($executionStartTime)
    {

        if ($this->task['task_email_notify'] === true) {
            $seconds = round(microtime(true) - $this->executionStartTime, 2);


            Notification::send(User::allUsersAndRecipients(), new MailTaskRunNotification($seconds, $this->task));
            Notification::send(User::all(), new DBNotification('Scheduled Task Completed', 'Task ID ' . $this->task['id'] . ' - "' . $this->task['task_name'] . '" was completed in ' . $executionStartTime, 'system', 'info', 'pficon-info'));
        }
    }
}
