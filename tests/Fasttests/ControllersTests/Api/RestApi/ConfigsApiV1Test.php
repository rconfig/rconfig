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
function configsApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns paginated configs', function () {
    Config::factory(3)->create();

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns a config', function () {
    $config = Config::factory()->create();

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/' . $config->id)
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $config->id]);
});

test('destroy removes config', function () {
    // Point at a path that does not exist so no real file is deleted; the
    // controller still removes the DB row and returns success.
    $config = Config::factory()->create([
        'config_location' => 'tests/storage/configs/does-not-exist-' . uniqid() . '.txt',
    ]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/configs/' . $config->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseMissing('configs', ['id' => $config->id]);
});

test('search get returns results', function () {
    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/search?searchTerm=hostname')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('search post returns results', function () {
    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/configs/search', ['searchTerm' => 'hostname'])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('search validates minimum length', function () {
    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/configs/search', ['searchTerm' => 'ab'])
        ->assertStatus(422)
        ->assertJsonFragment(['success' => false]);
});

test('status count for success', function () {
    Config::factory()->create(['device_id' => 778001, 'download_status' => 1]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/status/778001/success')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['count']]);
});

test('status count for failed', function () {
    Config::factory()->create(['device_id' => 778002, 'download_status' => 0]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/status/778002/failed')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['count']]);
});

test('status count for all', function () {
    Config::factory()->create(['device_id' => 778003, 'download_status' => 1]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/status/778003/all')
        ->assertStatus(200)
        ->assertJsonStructure(['data' => ['count']]);
});

test('status count with invalid status returns 422', function () {
    Config::factory()->create(['device_id' => 778004, 'download_status' => 1]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/status/778004/not-a-status')
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Invalid status']);
});

test('all by device id returns configs', function () {
    Config::factory(2)->create([
        'device_id' => 778005,
        'download_status' => 1,
        'config_location' => 'tests/storage/configs/missing-' . uniqid() . '.txt',
    ]);

    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/all-by-deviceid/778005')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('all by device id unknown device returns failure', function () {
    $this->withHeaders(configsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configs/all-by-deviceid/999777555')
        ->assertStatus(422)
        ->assertJsonFragment(['success' => false]);
});
