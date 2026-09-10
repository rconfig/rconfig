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
function usersApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns users', function () {
    User::factory(3)->create();

    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->getJson('/api/v1/users?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->getJson('/api/v1/users/' . $user->id)
        ->assertStatus(200)
        ->assertJsonFragment(['email' => $user->email]);
});

test('store creates user', function () {
    $email = 'rest-api-user-' . uniqid() . '@example.com';

    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->postJson('/api/v1/users', [
            'name' => 'Rest Api User',
            'username' => 'restapiuser' . rand(1000, 9999),
            'email' => $email,
            'password' => 'secretpass',
            'repeat_password' => 'secretpass',
            'role' => 'User',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('users', ['email' => $email]);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->postJson('/api/v1/users', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'repeat_password', 'role']);
});

test('update edits user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/users/' . $user->id, [
            'name' => 'Updated Name',
            'username' => 'updateduser' . rand(1000, 9999),
            'email' => $user->email,
            'password' => 'secretpass',
            'repeat_password' => 'secretpass',
            'role' => 'User',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
});

test('destroy deletes user', function () {
    $user = User::factory()->create();

    $this->withHeaders(usersApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/users/' . $user->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});
