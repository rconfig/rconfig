<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ArchiveLogsJob;
use App\Jobs\BackupRun;
use App\Jobs\TaskDownloadRun;
use App\Models\Task;
use App\Models\User;
use App\Notifications\DBNotification;
use App\Notifications\MailTaskRunNotification;
use App\Traits\LogsTaskActivity;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TaskManualRunController extends Controller
{
    use LogsTaskActivity, RespondsWithHttpStatus;

    protected $monitoredTask;

    /**
     * Run the specified task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function runManualTask(Request $request)
    {
        $executionStartTime = microtime(true);
        try {
            $task = Task::FindOrFail($request->id)->toArray();
        } catch (\Exception $e) {
            return $this->failureResponse($e->getMessage());
        }

        $this->logTaskStarted($task['id']);

        $download_commands = ['rconfig:download-device', 'rconfig:download-tag', 'rconfig:download-category'];
        if (in_array($task['task_command'], $download_commands)) {
            $task['task_command'] = 'rconfig:download-task';

            dispatch(new TaskDownloadRun($task));
            $executionEndTime = microtime(true);

            $this->logTaskFinished($task['id']);

            return $this->successResponse('TaskDownloadRun task pushed to queues successfully.');
        }

        $this->logTaskFinished($task['id']);

        return $this->failureResponse('Something went wrong, as no tasks were sent to the queues!');
    }

    private function logTheTask($task)
    {
        $logmsg = 'Task command "' . $task['task_command'] . ' ' . $task['id'] . '" was run with ID:' . $task['id'];
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'cron_scheduler');

        $this->logTaskFinished($task['id']);
    }

    private function _notifyTaskSend($executionStartTime, $task)
    {
        if ($task['task_email_notify'] == 1) {
            $seconds = round(microtime(true) - $executionStartTime, 2);
            Notification::send(User::all(), new MailTaskRunNotification($seconds, $task));
            Notification::send(User::all(), new DBNotification('Scheduled Task Completed', 'Task ID ' . $task['id'] . ' - "' . $task['task_name'] . '" was completed in ' . $executionStartTime, 'system', 'info', 'pficon-info'));
        }
    }
}
