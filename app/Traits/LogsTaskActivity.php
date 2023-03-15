<?php

namespace App\Traits;

use App\Models\MonitoredScheduledTaskLogItems;
use App\Models\MonitoredScheduledTasks;
use Artisan;

trait LogsTaskActivity
{
    protected $monitoredTask;

    private function logTaskStarted($task_id)
    {
        $this->monitoredTask = MonitoredScheduledTasks::where('task_id', $task_id);

        $this->checkIfNullAndReSyncTasks($task_id);

        MonitoredScheduledTaskLogItems::create(
            [
                'task_id' => $task_id,
                'monitored_scheduled_task_id' => $this->monitoredTask->first()->id,
                'meta' => 'Task started',
                'type' => $this->monitoredTask->first()->type,
                'created_at' => now(),
            ]
        );

        $this->monitoredTask->update([
            'last_started_at' => now(),
        ]);
    }

    private function logTaskFinished($task_id)
    {
        $this->monitoredTask = MonitoredScheduledTasks::where('task_id', $task_id);

        $this->checkIfNullAndReSyncTasks($task_id);

        MonitoredScheduledTaskLogItems::create(
            [
                'task_id' => $task_id,
                'monitored_scheduled_task_id' => $this->monitoredTask->first()->id,
                'meta' => 'Task finished',
                'type' => $this->monitoredTask->first()->type,
                'created_at' => now(),
            ]
        );

        $this->monitoredTask->update([
            'last_finished_at' => now(),
        ]);
    }

    private function logTaskFailed($task_id)
    {
        \Log::info('logTaskFailed: '.$task_id);
        $this->monitoredTask = MonitoredScheduledTasks::where('task_id', $task_id);

        $this->checkIfNullAndReSyncTasks($task_id);

        MonitoredScheduledTaskLogItems::create(
            [
                'task_id' => $task_id,
                'monitored_scheduled_task_id' => $this->monitoredTask->first()->id,
                'meta' => 'Task failed',
                'type' => $this->monitoredTask->first()->type,
                'created_at' => now(),
            ]
        );

        $this->monitoredTask->update([
            'last_finished_at' => now(),
        ]);
    }

    private function checkIfNullAndReSyncTasks($task_id)
    {
        if ($this->monitoredTask->first() === null) {
            Artisan::call('rconfig:sync-tasks');
            $this->monitoredTask = MonitoredScheduledTasks::where('task_id', $task_id);
        }
    }
}
