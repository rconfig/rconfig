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
            self::SYSTEM => 'System Events',
            self::CONFIG => 'Configuration Changes',
            self::CONNECTION => 'Device Connection Issues',
            self::TASK => 'Task Completion',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SYSTEM => 'General system notifications and alerts',
            self::CONFIG => 'Configuration downloads, changes, and management',
            self::CONNECTION => 'Device connection failures and network issues',
            self::TASK => 'Task completion notifications and reports',
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
