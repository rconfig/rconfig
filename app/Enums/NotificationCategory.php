<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case SYSTEM = 'system';
    case CONFIG = 'config';
    case CONNECTION = 'connection';
    case TASK = 'task';

    public function label(): string
    {
        return match ($this) {
            self::SYSTEM => 'notifications.categories.system',
            self::CONFIG => 'notifications.categories.config',
            self::CONNECTION => 'notifications.categories.connection',
            self::TASK => 'notifications.categories.task',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SYSTEM => 'notifications.categories.system_desc',
            self::CONFIG => 'notifications.categories.config_desc',
            self::CONNECTION => 'notifications.categories.connection_desc',
            self::TASK => 'notifications.categories.task_desc',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SYSTEM => 'pficon-info',
            self::CONFIG => 'pficon-settings',
            self::CONNECTION => 'pficon-disconnected',
            self::TASK => 'pficon-ok',
        };
    }
}
