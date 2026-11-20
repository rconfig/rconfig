<?php

use App\Jobs\SendTaskReportNotificationJob;
use App\Jobs\TaskCompleteNotificationJob;
use App\Jobs\TaskReportJob;
use App\Notifications\MailSendTaskReportNotification;
use App\Notifications\MailTaskCompleteNotification;
use App\Services\Notifications\TaskRunJobDispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

/**
 * Minimal report payload as built by rconfigTaskDownload::create_report().
 *
 * @return object{task: object, start_time: Carbon, end_time: Carbon, report_path: string}
 */
function taskRunReportData(bool $reportNotify, bool $completeNotify, string $reportPath = '/nonexistent/report.html'): object
{
    return (object) [
        'task' => (object) [
            'id' => 999,
            'task_name' => 'Nightly backup',
            'task_desc' => 'Test task',
            'download_report_notify' => $reportNotify,
            'task_email_notify' => $completeNotify,
        ],
        'start_time' => now()->subMinute(),
        'end_time' => now(),
        'report_path' => $reportPath,
    ];
}

/**
 * Regression for #386: the report email job used to be queued alongside the report job,
 * so it could run before the report file existed and be silently dropped.
 */
test('the report email is chained after the job that writes the report', function () {
    Bus::fake();

    (new TaskRunJobDispatcher)->dispatch(taskRunReportData(reportNotify: true, completeNotify: false));

    Bus::assertChained([TaskReportJob::class, SendTaskReportNotificationJob::class]);
    Bus::assertNotDispatched(SendTaskReportNotificationJob::class);
    Bus::assertNotDispatched(TaskCompleteNotificationJob::class);
});

test('the report is still built when the task does not send the report email', function () {
    Bus::fake();

    (new TaskRunJobDispatcher)->dispatch(taskRunReportData(reportNotify: false, completeNotify: false));

    Bus::assertDispatched(TaskReportJob::class, fn (TaskReportJob $job): bool => $job->chained === []);
    Bus::assertNotDispatched(SendTaskReportNotificationJob::class);
});

test('the task completed email is dispatched on its own when the task enables it', function () {
    Bus::fake();

    (new TaskRunJobDispatcher)->dispatch(taskRunReportData(reportNotify: true, completeNotify: true));

    Bus::assertChained([TaskReportJob::class, SendTaskReportNotificationJob::class]);
    Bus::assertDispatched(TaskCompleteNotificationJob::class);
});

/**
 * Regression for RCO-2244: these jobs went to the "default" queue, which no Horizon supervisor works.
 */
test('the report, report email and completed email are queued on rConfigDefault', function () {
    Queue::fake();

    (new TaskRunJobDispatcher)->dispatch(taskRunReportData(reportNotify: true, completeNotify: true));

    Queue::assertPushedOn('rConfigDefault', TaskReportJob::class, function (TaskReportJob $job): bool {
        return unserialize($job->chained[0])->queue === 'rConfigDefault';
    });
    Queue::assertPushedOn('rConfigDefault', TaskCompleteNotificationJob::class);
});

test('the report chain runs on the sync connection when asked', function () {
    Bus::fake();

    (new TaskRunJobDispatcher)->dispatch(taskRunReportData(reportNotify: true, completeNotify: false), true);

    Bus::assertDispatched(TaskReportJob::class, fn (TaskReportJob $job): bool => $job->connection === 'sync');
});

test('the report email has its own subject, distinct from the task completed email', function () {
    $data = taskRunReportData(reportNotify: true, completeNotify: true);

    $reportSubject = (new MailSendTaskReportNotification($data))->toMail(null)->subject;
    $completeSubject = (new MailTaskCompleteNotification($data))->toMail(null)->subject;

    expect($reportSubject)->toBe('Nightly backup - rConfig Download-Task Report')
        ->and($reportSubject)->not->toBe($completeSubject);
});

test('the report email attaches the report when the file exists', function () {
    $reportPath = tempnam(sys_get_temp_dir(), 'rconfig-report-');
    file_put_contents($reportPath, '<html>report</html>');

    try {
        $message = (new MailSendTaskReportNotification(taskRunReportData(true, false, $reportPath)))->toMail(null);

        expect($message->attachments)->toHaveCount(1)
            ->and($message->attachments[0]['file'])->toBe($reportPath);
    } finally {
        unlink($reportPath);
    }
});

test('the report email is still built without the attachment when the report file is missing', function () {
    Log::spy();

    $message = (new MailSendTaskReportNotification(taskRunReportData(true, false)))->toMail(null);

    expect($message->attachments)->toBe([]);
    Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $msg): bool => str_contains($msg, '/nonexistent/report.html'));
});
