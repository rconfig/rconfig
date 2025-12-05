<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;

class NotificationDefault extends Model
{
    protected $table = 'notifications_defaults';
    protected $fillable = [
        'notification_type',
        'category',
        'default_db',
        'default_mail',
        'description',
    ];
    protected $casts = [
        'default_db' => 'boolean',
        'default_mail' => 'boolean',
        'notification_type' => NotificationType::class,
        'category' => NotificationCategory::class,
    ];

    public function getDefaultForChannel(NotificationChannel $channel): bool
    {
        return match ($channel) {
            NotificationChannel::DB => $this->default_db,
            NotificationChannel::MAIL => $this->default_mail,
        };
    }

    public function setDefaultForChannel(NotificationChannel $channel, bool $enabled): void
    {
        match ($channel) {
            NotificationChannel::DB => $this->default_db = $enabled,
            NotificationChannel::MAIL => $this->default_mail = $enabled,
        };
    }
}
