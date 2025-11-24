<?php

// app/Traits/NotificationDispatcher.php

namespace App\Traits;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

trait NotificationDispatcher
{
    /**
     * Send notification to users based on their preferences
     *
     * @param  mixed  $notification
     * @param  array|null  $channels  - specific channels to send to, or null for all enabled channels
     * @return array - results per channel
     */
    protected function sendNotification(NotificationType $type, $notification, ?array $channels = null): array
    {
        $results = [];
        $channelsToSend = $channels ?? NotificationChannel::cases();

        foreach ($channelsToSend as $channel) {
            if (is_string($channel)) {
                $channel = NotificationChannel::from($channel);
            }

            $results[$channel->value] = $this->sendToChannel($type, $notification, $channel);
        }

        return $results;
    }

    /**
     * Send notification to a specific channel
     *
     * @param  mixed  $notification
     */
    protected function sendToChannel(NotificationType $type, $notification, NotificationChannel $channel): array
    {
        try {
            $recipients = User::getNotificationRecipients($type, $channel);

            if ($recipients->count() === 0) {
                return [
                    'success' => true,
                    'recipients' => 0,
                    'message' => "No recipients for {$type->value} via {$channel->value}",
                ];
            }

            // Handle different channel types
            switch ($channel) {

                case NotificationChannel::DB:
                case NotificationChannel::MAIL:
                    Notification::send($recipients, $notification);
                    break;
            }

            return [
                'success' => true,
                'recipients' => $recipients->count(),
                'message' => "Sent {$type->value} to {$recipients->count()} users via {$channel->value}",
            ];
        } catch (\Exception $e) {
            $error = "Failed to send {$type->value} via {$channel->value}: " . $e->getMessage();
            Log::error($error);

            return [
                'success' => false,
                'recipients' => 0,
                'error' => $error,
            ];
        }
    }

    /**
     * Send notification with error fallback
     * If primary notification fails, send error notification to system error recipients
     *
     * @param  mixed  $notification
     * @param  mixed|null  $errorNotification
     */
    protected function sendNotificationWithErrorFallback(
        NotificationType $type,
        $notification,
        ?array $channels = null,
        $errorNotification = null
    ): array {
        $results = $this->sendNotification($type, $notification, $channels);

        // Check if any channel failed
        $failures = array_filter($results, fn ($result) => ! $result['success']);

        if (! empty($failures) && $errorNotification) {
            // Send error notification to system error recipients
            foreach ($failures as $channel => $failure) {
                try {
                    $this->sendToChannel(
                        NotificationType::SYSTEM_NOTIFICATION_ERROR,
                        $errorNotification,
                        NotificationChannel::DB
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send error notification: ' . $e->getMessage());
                }
            }
        }

        return $results;
    }

    /**
     * Quick helper for single-channel notifications
     *
     * @param  mixed  $notification
     */
    protected function sendToSingleChannel(NotificationType $type, NotificationChannel $channel, $notification): array
    {
        return $this->sendToChannel($type, $notification, $channel);
    }

    /**
     * Send to all default channels for a notification type
     *
     * @param  mixed  $notification
     */
    protected function sendToDefaultChannels(NotificationType $type, $notification): array
    {
        $defaults = $type->defaultChannels();
        $enabledChannels = [];

        foreach ($defaults as $channelName => $enabled) {
            if ($enabled) {
                $enabledChannels[] = NotificationChannel::from($channelName);
            }
        }

        return $this->sendNotification($type, $notification, $enabledChannels);
    }
}
