<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class NotificationEnumsController extends Controller
{
    /**
     * Get all notification enums for frontend
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'categories' => $this->getCategories(),
            'channels' => $this->getChannels(),
            'types' => $this->getTypes(),
        ]);
    }

    private function getCategories(): array
    {
        return collect(NotificationCategory::cases())->map(function ($category) {
            return [
                'key' => $category->value,
                'label' => $category->label(),
                'description' => $category->description(),
                'icon' => $this->getIconForCategory($category->value),
            ];
        })->toArray();
    }

    private function getChannels(): array
    {
        return collect(NotificationChannel::cases())->map(function ($channel) {
            return [
                'key' => $channel->value,
                'label' => $channel->label(),
                'description' => $channel->description(),
                'icon' => $this->getIconForChannel($channel->value),
                'color' => $this->getColorForChannel($channel->value),
            ];
        })->toArray();
    }

    private function getTypes(): array
    {
        return collect(NotificationType::cases())->map(function ($type) {
            $defaults = $type->defaultChannels();

            return [
                'key' => $type->value,
                'category' => $type->category()->value,
                'label' => $type->label(),
                'description' => $type->description(),
                'severity' => $type->severity(),
                'channels' => array_keys(array_filter($defaults)),
                'defaults' => $defaults,
            ];
        })->toArray();
    }

    private function getIconForCategory(string $category): string
    {
        return match ($category) {
            'system' => 'Server',
            'config' => 'Settings',
            'backup' => 'HardDrive',
            'connection' => 'Wifi',
            'import' => 'Upload',
            'task' => 'CheckCircle',
            'authentication' => 'Shield',
            'compliance' => 'FileText',
            default => 'Bell'
        };
    }

    private function getIconForChannel(string $channel): string
    {
        return match ($channel) {
            'db' => 'Database',
            'mail' => 'Mail',
            default => 'Bell'
        };
    }

    private function getColorForChannel(string $channel): string
    {
        return match ($channel) {
            'db' => 'blue',
            'mail' => 'green',
            default => 'gray'
        };
    }

    private function isSlackEnabledForType(NotificationType $type): bool
    {
        return in_array($type, [
            NotificationType::CONFIG_CHANGED,
        ], true);
    }
}
