<?php

namespace App\Console;

use App\Jobs\CheckForUpdateJob;
use App\Jobs\TaskDownloadRun;
use App\Models\MonitoredScheduledTaskLogItems;
use App\Models\Task;
use App\Traits\LogsTaskActivity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Spatie\Health\Models\HealthCheckResultHistoryItem;

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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->schedule = $schedule;

        if (Schema::hasTable('tasks')) {
            // Get all tasks from the database
            $tasks = Task::all();

            // Go through each task to dynamically set them up.
            foreach ($tasks as $task) {

                // if task is_paused, skip it
                if ($task->is_paused) {
                    continue;
                }

                // Use the scheduler to add the task at its desired frequency
                $executionStartTime = microtime(true);
                $this->download_task($executionStartTime, $task);
            }
        }

        $schedule->command(RunHealthChecksCommand::class)->everyFiveMinutes();
        $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyFiveMinutes();
        $schedule->command('queue:prune-batches --hours=48 --unfinished=72')->daily();
        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItems::class])->daily();
        $schedule->command('model:prune', ['--model' => HealthCheckResultHistoryItem::class])->daily();
        $this->schedule->command('rconfig:config-summaries-sync')->dailyAt('3:00');
        $schedule->job(new CheckForUpdateJob)->weeklyOn(6, '03:00')->withoutOverlapping();
    }

    /**
     * Schedule a download task. Every supported task_command runs the fixed
     * rconfig:download-task command with the task id as its only argument.
     * A task with an unsupported task_command is skipped and logged.
     */
    private function download_task($executionStartTime, $task)
    {
        if (! in_array($task->task_command, TaskDownloadRun::ALLOWED_TASK_COMMANDS, true)) {
            Log::warning('Scheduler skipped a task with an unsupported task command.', [
                'task_id' => $task->id,
                'task_command' => $task->task_command,
            ]);

            return;
        }

        $task->task_command = TaskDownloadRun::RUN_COMMAND;

        $this->schedule->command(TaskDownloadRun::RUN_COMMAND, [(int) $task->id])
            ->cron(implode(' ', $task->task_cron))
            ->before(function () use ($task) {
                $this->logTaskStarted($task->id);
            })
            ->appendOutputTo(storage_path() . '/logs/laravel.log')
            ->after(function () use ($task) {
                $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" was run with ID:' . $task->id;
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
                $this->logTaskFinished($task->id);
            });
    }

    private function afterTaskRunActions($task, $executionStartTime)
    {
        $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" was run with ID:' . $task->id;
        $this->logTaskFinished($task->id);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
    }

    private function onTaskFailureActions($task, $executionStartTime)
    {
        $logmsg = 'Task command "' . $task->task_command . ' ' . $task->id . '" failed. See logs for more information.';
        $this->logTaskFailed($task->id);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
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
