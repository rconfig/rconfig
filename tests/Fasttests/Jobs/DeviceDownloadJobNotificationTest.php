<?php

namespace Tests\Fasttests\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Jobs\DeviceDownloadJob;
use App\Models\User;
use App\Notifications\DBDeviceConnectionFailureNotification;
use App\Notifications\MailDeviceConnectionFailureNotification;
use Illuminate\Support\Facades\Notification;
use ReflectionMethod;
use Tests\TestCase;

class DeviceDownloadJobNotificationTest extends TestCase
{
    /**
     * Invoke the job's protected failure-notification dispatch directly so we
     * can assert routing without needing a live device connection.
     */
    private function dispatchFailureNotifications(): void
    {
        $job = new DeviceDownloadJob(['id' => 999, 'device_name' => 'TestDevice'], 'device', false);

        $method = new ReflectionMethod($job, 'sendDeviceConnectionFailureNotifications');
        $method->setAccessible(true);
        $method->invoke($job, 'No config data returned for TestDevice');
    }

    public function test_device_failure_sends_both_db_and_mail_to_user_opted_in_to_both(): void
    {
        Notification::fake();

        $user = User::factory()->create(['get_notifications' => true]);
        $user->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
        $user->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, true);

        $this->dispatchFailureNotifications();

        Notification::assertSentTo($user, DBDeviceConnectionFailureNotification::class);
        Notification::assertSentTo($user, MailDeviceConnectionFailureNotification::class);
    }

    public function test_device_failure_respects_per_channel_preferences(): void
    {
        Notification::fake();

        $dbOnly = User::factory()->create(['get_notifications' => true]);
        $dbOnly->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
        $dbOnly->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, false);

        $this->dispatchFailureNotifications();

        Notification::assertSentTo($dbOnly, DBDeviceConnectionFailureNotification::class);
        Notification::assertNotSentTo($dbOnly, MailDeviceConnectionFailureNotification::class);
    }

    public function test_device_failure_skips_users_who_opted_out_of_all_notifications(): void
    {
        Notification::fake();

        $optedOut = User::factory()->create(['get_notifications' => false]);
        $optedOut->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::DB, true);
        $optedOut->setNotificationPreference(NotificationType::CONNECTION_DEVICE_FAILURE, NotificationChannel::MAIL, true);

        $this->dispatchFailureNotifications();

        Notification::assertNotSentTo($optedOut, DBDeviceConnectionFailureNotification::class);
        Notification::assertNotSentTo($optedOut, MailDeviceConnectionFailureNotification::class);
    }
}
