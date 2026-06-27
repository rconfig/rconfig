<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case DB = 'db';
    case MAIL = 'mail';

    public function label(): string
    {
        return match ($this) {
            self::DB => 'Database',
            self::MAIL => 'Email',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DB => 'pficon-database',
            self::MAIL => 'pficon-email',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DB => 'Notifications shown in your dashboard',
            self::MAIL => 'Sent to your email address',
        };
    }
}
