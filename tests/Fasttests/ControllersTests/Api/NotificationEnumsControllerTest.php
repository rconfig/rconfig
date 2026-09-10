<?php

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('it returns every notification type and channel', function () {
    $response = $this->getJson('/api/notification-enums');

    $response->assertOk();
    $response->assertJsonCount(count(NotificationType::cases()), 'types');
    $response->assertJsonCount(count(NotificationChannel::cases()), 'channels');
});

test('channel descriptions are resolved not translation keys', function () {
    $response = $this->getJson('/api/notification-enums');

    foreach ($response->json('channels') as $channel) {
        expect($channel['description'])->not->toBeEmpty();
        $this->assertStringNotContainsString('notifications.channels.', $channel['description']);
    }
});
