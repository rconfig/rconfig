<?php

namespace App\Services\Notifications;

use App\Jobs\SendTaskReportNotificationJob;
use App\Jobs\TaskCompleteNotificationJob;
use App\Jobs\TaskReportJob;
use Illuminate\Support\Facades\Bus;

class TaskRunJobDispatcher
{
    /**
     * Queue the report build and the notifications for a finished download task run.
     *
     * The Download Task Report email attaches the report file, so it is chained after
     * the job that writes that file. Queued separately, the email job could run first,
     * fail to attach the missing file and be dropped with only a log line.
     *
     * @param  object{task: object{download_report_notify: int|bool|null, task_email_notify: int|bool|null}}  $reportData
     */
    public function dispatch(object $reportData, bool $runReportSynchronously = false): void
    {
        $reportJobs = [new TaskReportJob($reportData)];

        if ((int) $reportData->task->download_report_notify === 1) {
            $reportJobs[] = new SendTaskReportNotificationJob($reportData);
        }

        $chain = Bus::chain($reportJobs);

        if ($runReportSynchronously) {
            $chain->onConnection('sync');
        }

        $chain->dispatch();

        if ((int) $reportData->task->task_email_notify === 1) {
            dispatch(new TaskCompleteNotificationJob($reportData));
        }
    }
}
