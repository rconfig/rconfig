<?php

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Jobs\DeviceDownloadJob;
use App\Models\User;
use App\Notifications\DBDeviceConnectionFailureNotification;
use App\Notifications\MailDeviceConnectionFailureNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * Invoke the job's protected failure-notification dispatch directly so we
 * can assert routing without needing a live device connection.
 */
function dispatchFailureNotifications(): void
{
    $job = new DeviceDownloadJob(['id' => 999, 'device_name' => 'TestDevice'], 'device', false);

    $method = new ReflectionMethod($job, 'sendDeviceConnectionFailureNotifications');
    $method->setAccessible(true);
    $method->invoke($job, 'No config data returned for TestDevice');
}

test('device failure sends both db and mail to user opted in to both', function () {
    Notification::fake();

    $user = User::factory()->create(['get_notifications' => true]);
    $user->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
    $user->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, true);

    dispatchFailureNotifications();

    Notification::assertSentTo($user, DBDeviceConnectionFailureNotification::class);
    Notification::assertSentTo($user, MailDeviceConnectionFailureNotification::class);
});

test('device failure respects per channel preferences', function () {
    Notification::fake();

    $dbOnly = User::factory()->create(['get_notifications' => true]);
    $dbOnly->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
    $dbOnly->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, false);

    dispatchFailureNotifications();

    Notification::assertSentTo($dbOnly, DBDeviceConnectionFailureNotification::class);
    Notification::assertNotSentTo($dbOnly, MailDeviceConnectionFailureNotification::class);
});

test('device failure skips users who opted out of all notifications', function () {
    Notification::fake();

    $optedOut = User::factory()->create(['get_notifications' => false]);
    $optedOut->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
    $optedOut->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, true);

    dispatchFailureNotifications();

    Notification::assertNotSentTo($optedOut, DBDeviceConnectionFailureNotification::class);
    Notification::assertNotSentTo($optedOut, MailDeviceConnectionFailureNotification::class);
});
