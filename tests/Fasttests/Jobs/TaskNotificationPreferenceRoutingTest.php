<?php

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Jobs\SendTaskReportNotificationJob;
use App\Jobs\TaskCompleteNotificationJob;
use App\Models\User;
use App\Notifications\DBTaskCompleteNotification;
use App\Notifications\MailSendTaskReportNotification;
use App\Notifications\MailTaskCompleteNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->beginTransaction();
    Notification::fake();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * Minimal report payload as built by rconfigTaskDownload::create_report().
 */
function taskNotificationReportData(): object
{
    return (object) [
        'task' => (object) ['id' => 999, 'task_name' => 'Nightly backup', 'task_desc' => 'Test task'],
        'start_time' => now()->subMinute(),
        'end_time' => now(),
        'report_path' => '/nonexistent/report.html',
    ];
}

/**
 * Create a user with explicit preferences for both task notification types on one channel.
 * Explicit rows are required because both types default to enabled in notifications_defaults.
 */
function taskNotificationUser(NotificationChannel $channel, bool $completed, bool $report, bool $getNotifications = true): User
{
    $user = User::factory()->create(['get_notifications' => $getNotifications]);
    $user->setNotificationPreference(NotificationType::TASK_COMPLETED, $channel, $completed);
    $user->setNotificationPreference(NotificationType::TASK_DOWNLOAD_REPORT, $channel, $report);

    return $user;
}

test('report email reaches a user subscribed only to Download Task Report', function () {
    $user = taskNotificationUser(NotificationChannel::MAIL, completed: false, report: true);

    (new SendTaskReportNotificationJob(taskNotificationReportData()))->handle();
    (new TaskCompleteNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertSentToTimes($user, MailSendTaskReportNotification::class, 1);
    Notification::assertNotSentTo($user, MailTaskCompleteNotification::class);
});

test('report email does not reach a user subscribed only to Task Completed', function () {
    $user = taskNotificationUser(NotificationChannel::MAIL, completed: true, report: false);

    (new SendTaskReportNotificationJob(taskNotificationReportData()))->handle();
    (new TaskCompleteNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertSentToTimes($user, MailTaskCompleteNotification::class, 1);
    Notification::assertNotSentTo($user, MailSendTaskReportNotification::class);
});

test('a user subscribed to both gets exactly one of each email', function () {
    $user = taskNotificationUser(NotificationChannel::MAIL, completed: true, report: true);

    (new SendTaskReportNotificationJob(taskNotificationReportData()))->handle();
    (new TaskCompleteNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertSentToTimes($user, MailSendTaskReportNotification::class, 1);
    Notification::assertSentToTimes($user, MailTaskCompleteNotification::class, 1);
});

test('report in-app notification follows the Download Task Report preference', function () {
    $reportOnly = taskNotificationUser(NotificationChannel::DB, completed: false, report: true);
    $completedOnly = taskNotificationUser(NotificationChannel::DB, completed: true, report: false);

    (new SendTaskReportNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertSentToTimes($reportOnly, DBTaskCompleteNotification::class, 1);
    Notification::assertNotSentTo($completedOnly, DBTaskCompleteNotification::class);
});

test('task complete in-app notification follows the Task Completed preference', function () {
    $reportOnly = taskNotificationUser(NotificationChannel::DB, completed: false, report: true);
    $completedOnly = taskNotificationUser(NotificationChannel::DB, completed: true, report: false);

    (new TaskCompleteNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertSentToTimes($completedOnly, DBTaskCompleteNotification::class, 1);
    Notification::assertNotSentTo($reportOnly, DBTaskCompleteNotification::class);
});

test('neither task notification reaches a user who opted out of all notifications', function () {
    $user = taskNotificationUser(NotificationChannel::MAIL, completed: true, report: true, getNotifications: false);

    (new SendTaskReportNotificationJob(taskNotificationReportData()))->handle();
    (new TaskCompleteNotificationJob(taskNotificationReportData()))->handle();

    Notification::assertNotSentTo($user, MailSendTaskReportNotification::class);
    Notification::assertNotSentTo($user, MailTaskCompleteNotification::class);
});
