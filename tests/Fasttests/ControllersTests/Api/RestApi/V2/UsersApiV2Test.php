<?php

use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function usersApiV2AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns users', function () {
    User::factory(3)->create();

    $this->withHeaders(usersApiV2AuthHeader($this->token))
        ->getJson('/api/v2/users?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV2AuthHeader($this->token))
        ->getJson('/api/v2/users/' . $user->id)
        ->assertStatus(200)
        ->assertJsonFragment(['email' => $user->email]);
});

test('store creates user', function () {
    $email = 'rest-api-v2-user-' . uniqid() . '@example.com';

    $this->withHeaders(usersApiV2AuthHeader($this->token))
        ->postJson('/api/v2/users', [
            'name' => 'Rest Api V2 User',
            'username' => 'restapiv2user' . rand(1000, 9999),
            'email' => $email,
            'password' => 'secretpass',
            'repeat_password' => 'secretpass',
            'role' => 'User',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('users', ['email' => $email]);
});

test('update edits user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV2AuthHeader($this->token))
        ->patchJson('/api/v2/users/' . $user->id, [
            'name' => 'Updated V2 Name',
            'username' => 'updatedv2user' . rand(1000, 9999),
            'email' => $user->email,
            'password' => 'secretpass',
            'repeat_password' => 'secretpass',
            'role' => 'User',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated V2 Name']);
});

test('destroy deletes user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV2AuthHeader($this->token))
        ->deleteJson('/api/v2/users/' . $user->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
