<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserNotificationPreferencesController extends Controller
{
    /**
     * Get user's notification preferences
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->getAllNotificationPreferences();

        return response()->json([
            'preferences' => $preferences,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Update a specific notification preference
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_type' => 'required|string',
            'channel' => 'required|string|in:db,mail',
            'enabled' => 'required|boolean',
        ]);

        try {
            $notificationType = NotificationType::from($validated['notification_type']);
            $channel = NotificationChannel::from($validated['channel']);
        } catch (\ValueError $e) {
            return response()->json([
                'message' => 'Invalid notification type or channel',
                'error' => $e->getMessage(),
            ], 422);
        }

        $user = $request->user();
        $user->setNotificationPreference($notificationType, $channel, $validated['enabled']);

        return response()->json([
            'message' => 'Notification preference updated successfully',
            'notification_type' => $validated['notification_type'],
            'channel' => $validated['channel'],
            'enabled' => $validated['enabled'],
        ]);
    }
}
