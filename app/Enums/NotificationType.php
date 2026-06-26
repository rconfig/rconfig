<?php

// app/Enums/NotificationType.php

namespace App\Enums;

enum NotificationType: string
{
    // System notifications
    case SYSTEM_NOTIFICATION_ERROR = 'system.notification_error';

    // Config notifications
    case CONFIG_DOWNLOAD_COMPLETED = 'config.download_completed';
    case CONFIG_PURGE_COMPLETED = 'config.purge_completed';
    case CONFIG_PURGE_FAILED_COMPLETED = 'config.purge_failed_completed';
    case CONFIG_CHANGED = 'config.changed';

    // Connection notifications
    case CONNECTION_DEVICE_FAILURE = 'connection.device_failure';

    // Task notifications
    case TASK_COMPLETED = 'task.completed';
    case TASK_DOWNLOAD_REPORT = 'task.download_report';

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => NotificationCategory::SYSTEM,

            self::CONFIG_DOWNLOAD_COMPLETED,
            self::CONFIG_PURGE_COMPLETED,
            self::CONFIG_PURGE_FAILED_COMPLETED,
            self::CONFIG_CHANGED => NotificationCategory::CONFIG,

            self::CONNECTION_DEVICE_FAILURE => NotificationCategory::CONNECTION,

            self::TASK_COMPLETED,
            self::TASK_DOWNLOAD_REPORT => NotificationCategory::TASK,

        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => 'Notification System Error',
            self::CONFIG_DOWNLOAD_COMPLETED => 'Configuration Download Completed',
            self::CONFIG_PURGE_COMPLETED => 'Configuration Purge Completed',
            self::CONFIG_PURGE_FAILED_COMPLETED => 'Failed Configuration Purge Completed',
            self::CONFIG_CHANGED => 'Configuration Changed',
            self::CONNECTION_DEVICE_FAILURE => 'Device Connection Failure',
            self::TASK_COMPLETED => 'Task Completed',
            self::TASK_DOWNLOAD_REPORT => 'Download Task Report',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => 'Alerts when notification system encounters errors',
            self::CONFIG_DOWNLOAD_COMPLETED => 'Notifications when manual config downloads complete',
            self::CONFIG_PURGE_COMPLETED => 'Alerts when configuration purge operations finish',
            self::CONFIG_PURGE_FAILED_COMPLETED => 'Notifications when failed config purge operations complete',
            self::CONFIG_CHANGED => 'Email alerts when a device configuration changes between versions',
            self::CONNECTION_DEVICE_FAILURE => 'Alerts when devices fail to connect via SSH, Telnet, or API',
            self::TASK_COMPLETED => 'Notifications when scheduled tasks complete',
            self::TASK_DOWNLOAD_REPORT => 'Email reports for download task completion',
        };
    }

    public function defaultChannels(): array
    {
        return match ($this) {
            // Critical alerts - all channels by default
            self::CONNECTION_DEVICE_FAILURE => ['db' => true, 'mail' => true],

            // Regular operations - DB only
            self::CONFIG_DOWNLOAD_COMPLETED => ['db' => true, 'mail' => true],
            self::CONFIG_PURGE_COMPLETED => ['db' => true, 'mail' => true],
            self::CONFIG_PURGE_FAILED_COMPLETED => ['db' => true, 'mail' => true],

            // Config change alerts - email only
            self::CONFIG_CHANGED => ['db' => false, 'mail' => true],

            // Reports - Mail preferred
            self::TASK_COMPLETED => ['db' => true, 'mail' => true],
            self::TASK_DOWNLOAD_REPORT => ['db' => true, 'mail' => true],

            // Errors - All channels
            self::SYSTEM_NOTIFICATION_ERROR => ['db' => true, 'mail' => true],
        };
    }

    public function severity(): string
    {
        return match ($this) {
            self::CONNECTION_DEVICE_FAILURE => 'warning',

            default => 'info',
        };
    }
}
