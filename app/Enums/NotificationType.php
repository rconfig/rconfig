<?php

// app/Enums/NotificationType.php

namespace App\Enums;

enum NotificationType: string
{
    // System notifications
    case SYSTEM_NOTIFICATION_ERROR = 'system.notification_error';

    // Config notifications
    case CONFIG_CHANGED = 'config.changed';
    case CONFIG_DOWNLOAD_COMPLETED = 'config.download_completed';
    case CONFIG_PURGE_COMPLETED = 'config.purge_completed';
    case CONFIG_PURGE_FAILED_COMPLETED = 'config.purge_failed_completed';

    // Connection notifications
    case CONNECTION_DEVICE_FAILURE = 'connection.device_failure';

    // Task notifications
    case TASK_COMPLETED = 'task.completed';
    case TASK_DOWNLOAD_REPORT = 'task.download_report';

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => NotificationCategory::SYSTEM,

            self::CONFIG_CHANGED,
            self::CONFIG_DOWNLOAD_COMPLETED,
            self::CONFIG_PURGE_COMPLETED,
            self::CONFIG_PURGE_FAILED_COMPLETED => NotificationCategory::CONFIG,

            self::CONNECTION_DEVICE_FAILURE => NotificationCategory::CONNECTION,

            self::TASK_COMPLETED,
            self::TASK_DOWNLOAD_REPORT => NotificationCategory::TASK,

        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => 'notifications.types.system_notification_error',
            self::CONFIG_CHANGED => 'notifications.types.config_changed',
            self::CONFIG_DOWNLOAD_COMPLETED => 'notifications.types.config_download_completed',
            self::CONFIG_PURGE_COMPLETED => 'notifications.types.config_purge_completed',
            self::CONFIG_PURGE_FAILED_COMPLETED => 'notifications.types.config_purge_failed_completed',
            self::CONNECTION_DEVICE_FAILURE => 'notifications.types.connection_device_failure',
            self::TASK_COMPLETED => 'notifications.types.task_completed',
            self::TASK_DOWNLOAD_REPORT => 'notifications.types.task_download_report',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SYSTEM_NOTIFICATION_ERROR => 'notifications.types.system_notification_error_desc',
            self::CONFIG_CHANGED => 'notifications.types.config_changed_desc',
            self::CONFIG_DOWNLOAD_COMPLETED => 'notifications.types.config_download_completed_desc',
            self::CONFIG_PURGE_COMPLETED => 'notifications.types.config_purge_completed_desc',
            self::CONFIG_PURGE_FAILED_COMPLETED => 'notifications.types.config_purge_failed_completed_desc',
            self::CONNECTION_DEVICE_FAILURE => 'notifications.types.connection_device_failure_desc',
            self::TASK_COMPLETED => 'notifications.types.task_completed_desc',
            self::TASK_DOWNLOAD_REPORT => 'notifications.types.task_download_report_desc',
        };
    }

    public function defaultChannels(): array
    {
        return match ($this) {
            // Critical alerts - all channels by default
            self::CONNECTION_DEVICE_FAILURE => ['db' => true, 'mail' => true],

            // Important operations - DB + Mail
            self::CONFIG_CHANGED => ['db' => true, 'mail' => true],

            // Regular operations - DB only
            self::CONFIG_DOWNLOAD_COMPLETED => ['db' => true, 'mail' => true],
            self::CONFIG_PURGE_COMPLETED => ['db' => true, 'mail' => true],
            self::CONFIG_PURGE_FAILED_COMPLETED => ['db' => true, 'mail' => true],

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
            self::CONNECTION_DEVICE_FAILURE,
            self::CONFIG_CHANGED => 'warning',

            default => 'info',
        };
    }
}
