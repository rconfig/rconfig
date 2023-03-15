<?php

namespace App\Console;

use App\Jobs\ArchiveLogsJob;
use App\Jobs\BackupCleanJob;
use App\Jobs\BackupRun;
use App\Jobs\PurgeConfigsJob;
use App\Models\Task;
use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailTaskRunNotification;
use App\Traits\LogsTaskActivity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

class Kernel extends ConsoleKernel
{
    use LogsTaskActivity;

    protected $schedule;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\rconfigTagList::class,
        Commands\rconfigTagDownload::class,
        Commands\rconfigCatList::class,
        Commands\rconfigCatDownload::class,
        Commands\rconfigDeviceList::class,
        Commands\rconfigDeviceDownload::class,
        Commands\rconfigTaskList::class,
        Commands\rconfigTaskDownload::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->schedule = $schedule;
        //running this to fix logging permissions issue every day in case of issues
        $this->schedule->exec('chown -R apache /var/www/html')
            ->dailyAt('8:00')
            ->after(function () {
                $logmsg = 'Exec Command chown -R apache was run on html dir';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
            });

        if (Schema::hasTable('tasks')) {
            // Get all tasks from the database
            $tasks = Task::all();

            // Go through each task to dynamically set them up.
            foreach ($tasks as $task) {
                // Use the scheduler to add the task at its desired frequency
                $executionStartTime = microtime(true);
                if ($task->task_command === 'rconfig:purge-configs') {
                    $this->purge_configs($executionStartTime, $task);
                } else {
                    // everything other than backup jobs
                    $this->download_task($executionStartTime, $task);
                }
                // exec('chown -R apache /var/www/html');
            }
        }

        $schedule->command(RunHealthChecksCommand::class)->everyMinute();
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])->daily();
    }

    private function download_task($executionStartTime, $task)
    {
        // Tasks Commands are stored in DB as the items in the $download_commands array. But they actually need to run as rconfig:download:task
        // Next transforms the $download_commands, to download-task commands. - Bit Hacky
        $download_commands = ['rconfig:download-device', 'rconfig:download-tag', 'rconfig:download-category'];
        if (in_array($task->task_command, $download_commands)) {
            $task->task_command = 'rconfig:download-task';
        }
        $this->schedule->command($task->task_command . ' ' . $task->id)
            ->cron(implode(' ', $task->task_cron))
            ->before(function () use ($task) {
                $this->logTaskStarted($task->id);
            })
            ->appendOutputTo(storage_path() . '/logs/laravel.log')
            ->after(function () use ($task, $executionStartTime) {
                $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" was run with ID:' . $task->id;
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
                $this->logTaskFinished($task->id);
                $this->_notifyTaskSend($executionStartTime, $task);
            });
    }

    private function _notifyTaskSend($executionStartTime, $task)
    {
        if ($task->task_email_notify == 1) {
            $seconds = round(microtime(true) - $executionStartTime, 2);
            try {
                Notification::send(User::all(), new MailTaskRunNotification($seconds, $task));
            } catch (\Throwable $th) {
                Log::error('MailTaskRunNotification Error:' . $th->getMessage());
            }
            try {
                Notification::send(User::all(), new DBNotification('Scheduled Task Completed', 'Task ID ' . $task->id . ' - "' . $task->task_name . '" was completed in ' . $executionStartTime, 'system', 'info', 'pficon-info'));
            } catch (\Throwable $th) {
                Log::error('DBNotification Error:' . $th->getMessage());
            }
        }
    }

    private function afterTaskRunActions($task, $executionStartTime)
    {
        $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" was run with ID:' . $task->id;
        $this->logTaskFinished($task->id);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
        $this->_notifyTaskSend($executionStartTime, $task);
    }

    private function onTaskFailureActions($task, $executionStartTime)
    {
        $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" failed. See logs for more information.';
        $this->logTaskFailed($task->id);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
        $this->_notifyTaskSend($executionStartTime, $task);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
