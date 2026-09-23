<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TaskDownloadRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Task commands that may legitimately be dispatched to the manual run queue.
     * They are all normalised to rconfig:download-task before execution, mirroring
     * the controller and scheduler. Anything outside this set is rejected so a
     * crafted queue payload cannot turn this job into an arbitrary Artisan runner.
     *
     * @var list<string>
     */
    private const ALLOWED_TASK_COMMANDS = [
        'rconfig:download-task',
        'rconfig:download-device',
        'rconfig:download-tag',
        'rconfig:download-category',
    ];

    /**
     * The Artisan command actually executed by this job.
     */
    private const RUN_COMMAND = 'rconfig:download-task';

    /**
     * @var array{task_command?: mixed, id?: mixed}
     */
    protected $task;

    protected $executionStartTime;

    /**
     * @param  array{task_command?: mixed, id?: mixed}  $task
     */
    public function __construct($task)
    {
        $this->task = $task;
        $this->executionStartTime = microtime(true);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $command = is_string($this->task['task_command'] ?? null) ? $this->task['task_command'] : '';

        if (! in_array($command, self::ALLOWED_TASK_COMMANDS, true)) {
            Log::warning('TaskDownloadRun rejected an unexpected task command.', [
                'task_command' => $command,
            ]);

            return;
        }

        if (! is_numeric($this->task['id'] ?? null)) {
            Log::warning('TaskDownloadRun rejected a non-numeric task id.', [
                'id' => $this->task['id'] ?? null,
            ]);

            return;
        }

        $taskId = (int) $this->task['id'];

        Artisan::call(self::RUN_COMMAND, ['taskid' => [$taskId]]);
        $result = Artisan::output();
        $arr = explode("\n", $result);
        Log::info(print_r($arr, true));

        $logmsg = 'Task command "' . self::RUN_COMMAND . ' ' . $taskId . '" was run with ID:' . $taskId;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');
    }
}
