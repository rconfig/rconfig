<?php

use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('create returns plaintext token once', function () {
    $response = $this->postJson('/api/settings/rest-api-token', [
        'api_token_name' => 'my-automation',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'api_token_name', 'api_token']]);

    $plaintext = $response->json('data.api_token');
    expect($plaintext)->not->toBeEmpty();
    $this->assertDatabaseHas('rest_api_tokens', ['api_token_name' => 'my-automation']);
});

test('index never exposes the raw token', function () {
    $token = RestApiToken::factory()->create(['api_token_name' => 'listed-token']);

    $response = $this->getJson('/api/settings/rest-api-token');

    $response->assertStatus(200)
        ->assertJsonFragment(['api_token_name' => 'listed-token']);

    $payload = $response->json('data');
    foreach ($payload as $row) {
        $this->assertArrayNotHasKey('api_token', $row);
    }

    $this->assertStringNotContainsString($token->api_token, $response->getContent());
});

test('duplicate name is rejected', function () {
    RestApiToken::factory()->create(['api_token_name' => 'dupe']);

    $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'dupe'])
        ->assertStatus(422);
});

test('delete removes token', function () {
    $token = RestApiToken::factory()->create();

    $this->deleteJson('/api/settings/rest-api-token/' . $token->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('rest_api_tokens', ['id' => $token->id]);
});
