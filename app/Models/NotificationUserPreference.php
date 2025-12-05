<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUserPreference extends Model
{
    protected $table = 'notifications_user_preferences';
    protected $fillable = [
        'user_id',
        'notification_type',
        'channel',
        'enabled',
    ];
    protected $casts = [
        'enabled' => 'boolean',
        'notification_type' => NotificationType::class,
        'channel' => NotificationChannel::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultSetting(): BelongsTo
    {
        return $this->belongsTo(NotificationDefault::class, 'notification_type', 'notification_type');
    }
}
