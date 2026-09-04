<?php

use App\Models\Config;
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
function configsApiV2AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns paginated configs', function () {
    Config::factory(3)->create();

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns a config', function () {
    $config = Config::factory()->create();

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/' . $config->id)
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $config->id]);
});

test('destroy removes config', function () {
    $config = Config::factory()->create([
        'config_location' => 'tests/storage/configs/does-not-exist-' . uniqid() . '.txt',
    ]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->deleteJson('/api/v2/configs/' . $config->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseMissing('configs', ['id' => $config->id]);
});

test('search get returns results', function () {
    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/search?searchTerm=hostname')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('search post returns results', function () {
    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->postJson('/api/v2/configs/search', ['searchTerm' => 'hostname'])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('status count for success', function () {
    Config::factory()->create(['device_id' => 779001, 'download_status' => 1]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/status/779001/success')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['count']]);
});

test('status count invalid status returns 422', function () {
    Config::factory()->create(['device_id' => 779002, 'download_status' => 1]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/status/779002/not-a-status')
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Invalid status']);
});
