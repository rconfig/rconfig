<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'external_links' => 'array',
        'onboarding_status' => 'array',
        'get_notifications' => 'boolean',
        'is_socialite' => 'integer',
        'is_socialite_approved' => 'integer',
    ];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/settings/users',
        );
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationUserPreference::class);
    }

    public function getNotificationPreference(NotificationType $type, NotificationChannel $channel): bool
    {
        // Check if user has a specific preference
        $preference = $this->notificationPreferences()
            ->where('notification_type', $type->value)
            ->where('channel', $channel->value)
            ->first();

        if ($preference) {
            return $preference->enabled;
        }

        // Fall back to system default
        $default = NotificationDefault::where('notification_type', $type->value)->first();
        if ($default) {
            return $default->getDefaultForChannel($channel);
        }

        // Ultimate fallback
        return match ($channel) {
            NotificationChannel::DB => true,
            NotificationChannel::MAIL => false,
        };
    }

    public function setNotificationPreference(NotificationType $type, NotificationChannel $channel, bool $enabled): void
    {
        NotificationUserPreference::updateOrCreate(
            [
                'user_id' => $this->id,
                'notification_type' => $type->value,
                'channel' => $channel->value,
            ],
            ['enabled' => $enabled]
        );
    }

    public static function getNotificationRecipients(?NotificationType $type = null, ?NotificationChannel $channel = null)
    {
         $query = static::whereNotNull('created_at');

        if ($type && $channel) {
            $query->where(function ($q) use ($type, $channel) {
                // Users with explicit preference enabled
                $q->whereHas('notificationPreferences', function ($subQ) use ($type, $channel) {
                    $subQ->where('notification_type', $type->value)
                        ->where('channel', $channel->value)
                        ->where('enabled', true);
                })
                    // OR users without preference but system default is enabled
                    ->orWhere(function ($subQ) use ($type, $channel) {
                        $subQ->whereDoesntHave('notificationPreferences', function ($prefQ) use ($type, $channel) {
                            $prefQ->where('notification_type', $type->value)
                                ->where('channel', $channel->value);
                        })
                            ->whereExists(function ($defaultQ) use ($type, $channel) {
                                $defaultQ->select(\DB::raw(1))
                                    ->from('notifications_defaults')
                                    ->where('notification_type', $type->value)
                                    ->where("default_{$channel->value}", true);
                            });
                    });
            });
        }

        return $query->get();
    }

    public static function allUsersAndRecipients()
    {
        return static::getNotificationRecipients();
    }

    public function setNotificationPreferences(array $preferences): void
    {
        foreach ($preferences as $typeValue => $channels) {
            $type = NotificationType::from($typeValue);

            foreach ($channels as $channelValue => $enabled) {
                $channel = NotificationChannel::from($channelValue);
                $this->setNotificationPreference($type, $channel, $enabled);
            }
        }
    }

    public function getAllNotificationPreferences(): array
    {
         $preferences = [];
        foreach (NotificationType::cases() as $type) {
            foreach (NotificationChannel::cases() as $channel) {
                $preferences[$type->value][$channel->value] = $this->getNotificationPreference($type, $channel);
            }
        }

        return $preferences;
    }
}
