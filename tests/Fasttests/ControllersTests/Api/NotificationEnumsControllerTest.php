<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;
use Tests\TestCase;

class NotificationEnumsControllerTest extends TestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_it_returns_every_notification_type_and_channel(): void
    {
        $response = $this->getJson('/api/notification-enums');

        $response->assertOk();
        $response->assertJsonCount(count(NotificationType::cases()), 'types');
        $response->assertJsonCount(count(NotificationChannel::cases()), 'channels');
    }

    public function test_channel_descriptions_are_resolved_not_translation_keys(): void
    {
        $response = $this->getJson('/api/notification-enums');

        foreach ($response->json('channels') as $channel) {
            $this->assertNotEmpty($channel['description']);
            $this->assertStringNotContainsString('notifications.channels.', $channel['description']);
        }
    }
}
