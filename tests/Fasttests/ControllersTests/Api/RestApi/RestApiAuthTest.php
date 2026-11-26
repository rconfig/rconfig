<?php

use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    // Ensure a system user exists for the token middleware to act as.
    User::factory()->create();
});

test('missing token returns 401', function () {
    $this->getJson('/api/v1/apitest')->assertStatus(401);
    $this->getJson('/api/v2/apitest')->assertStatus(401);
});

test('invalid token returns 401', function () {
    $this->withHeaders(['apitoken' => 'not-a-real-token'])
        ->getJson('/api/v1/apitest')
        ->assertStatus(401);
});

test('valid token in header returns 200', function () {
    $token = RestApiToken::factory()->create();

    $this->withHeaders(['apitoken' => $token->api_token])
        ->getJson('/api/v1/apitest')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('valid token in query returns 200', function () {
    $token = RestApiToken::factory()->create();

    $this->getJson('/api/v2/apitest?apitoken=' . $token->api_token)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('disabled token returns 401', function () {
    $token = RestApiToken::factory()->create(['api_token_status' => 0]);

    $this->withHeaders(['apitoken' => $token->api_token])
        ->getJson('/api/v1/apitest')
        ->assertStatus(401);

    $this->getJson('/api/v2/apitest?apitoken=' . $token->api_token)
        ->assertStatus(401);
});

test('a disabled token does not block an active one', function () {
    RestApiToken::factory()->create(['api_token_status' => 0]);
    $active = RestApiToken::factory()->create();

    $this->withHeaders(['apitoken' => $active->api_token])
        ->getJson('/api/v1/apitest')
        ->assertStatus(200);
});

test('token with no status still returns 200', function () {
    $token = RestApiToken::factory()->create(['api_token_status' => null]);

    $this->withHeaders(['apitoken' => $token->api_token])
        ->getJson('/api/v1/apitest')
        ->assertStatus(200);

    $this->getJson('/api/v2/apitest?apitoken=' . $token->api_token)
        ->assertStatus(200);
});

test('legacy api token param returns 422', function () {
    $token = RestApiToken::factory()->create();

    $this->getJson('/api/v1/apitest?api_token=' . $token->api_token)
        ->assertStatus(422);
});
