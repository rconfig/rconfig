<?php

namespace App\Console\Commands;

use App\Models\MonitoredScheduledTasks;
use App\Models\Task;
use Illuminate\Console\Command;

class MonitoredTaskSync extends Command
{
    protected $signature = 'rconfig:sync-tasks';

    public $description = 'Sync the schedule of the app with the schedule monitor';

    public function handle()
    {
        $this->info('Syncing tasks...');

        $this->syncScheduledTasksWithDatabase();

        $monitoredScheduledTasksCount = MonitoredScheduledTasks::count();

        $this->info('Syncing '.$monitoredScheduledTasksCount.' tasks...');
    }

    protected function syncScheduledTasksWithDatabase(): self
    {
        $this->info('Start syncing schedule with database...');

        $monitoredScheduledTasks = Task::all();
        foreach ($monitoredScheduledTasks as $task) {
            MonitoredScheduledTasks::updateOrCreate(
                ['task_id' => $task->id],
                [
                    'task_id' => $task->id,
                    'name' => $task->task_name,
                    'cron_expression' => implode(' ', $task->task_cron),
                    'type' => $task->task_command,
                ]
            );
        }

        return $this;
    }
}
