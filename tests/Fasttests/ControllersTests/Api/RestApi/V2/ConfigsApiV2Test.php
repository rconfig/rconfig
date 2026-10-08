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

test('index omits config contents unless includeConfig is passed', function () {
    $config = Config::factory()->create(['device_id' => 779101]);

    $response = $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs?filter[device_id]=779101')
        ->assertStatus(200);

    expect($response->json('data.0.id'))->toBe($config->id)
        ->and($response->json('data.0'))->not->toHaveKey('config');
});

test('index includes config contents when includeConfig is true', function () {
    $path = tempnam(sys_get_temp_dir(), 'rcfg');
    file_put_contents($path, "hostname router1\n");
    Config::factory()->create(['device_id' => 779102, 'config_location' => $path]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs?filter[device_id]=779102&includeConfig=true')
        ->assertStatus(200)
        ->assertJsonPath('data.0.config', "hostname router1\n");

    unlink($path);
});

test('show includes config contents when includeConfig is true', function () {
    $path = tempnam(sys_get_temp_dir(), 'rcfg');
    file_put_contents($path, "interface Gi0/0\n");
    $config = Config::factory()->create(['config_location' => $path]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/' . $config->id . '?includeConfig=1')
        ->assertStatus(200)
        ->assertJsonPath('id', $config->id)
        ->assertJsonPath('config', "interface Gi0/0\n");

    unlink($path);
});

test('show omits config contents without includeConfig', function () {
    $config = Config::factory()->create();

    $response = $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/' . $config->id)
        ->assertStatus(200);

    expect($response->json())->not->toHaveKey('config');
});

test('show reports a missing file in the config field', function () {
    $config = Config::factory()->create([
        'config_location' => 'tests/storage/configs/does-not-exist-' . uniqid() . '.txt',
    ]);

    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/' . $config->id . '?includeConfig=true')
        ->assertStatus(200)
        ->assertJsonPath('config', 'File does not exist at path ' . $config->config_location);
});

test('show returns 404 for an unknown config', function () {
    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs/999999999')
        ->assertStatus(404);
});

test('index filters by device_id, device_name and created_at', function () {
    $match = Config::factory()->create([
        'device_id' => 779103,
        'device_name' => 'edge-router-779103',
        'created_at' => '2022-03-18 06:46:06',
    ]);
    Config::factory()->create(['device_id' => 779104, 'device_name' => 'core-switch-779104']);

    $headers = configsApiV2AuthHeader($this->token);

    $this->withHeaders($headers)
        ->getJson('/api/v2/configs?filter[device_id]=779103')
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $match->id);

    $this->withHeaders($headers)
        ->getJson('/api/v2/configs?filter[device_name]=router-7791')
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $match->id);

    $this->withHeaders($headers)
        ->getJson('/api/v2/configs?filter[device_id]=779103&filter[created_at]=2022-03-18')
        ->assertJsonPath('total', 1);

    $this->withHeaders($headers)
        ->getJson('/api/v2/configs?filter[device_id]=779103&filter[created_at]=2022-03-19')
        ->assertJsonPath('total', 0);
});

test('index limits columns with fields and still includes config contents', function () {
    $path = tempnam(sys_get_temp_dir(), 'rcfg');
    file_put_contents($path, "hostname router2\n");
    Config::factory()->create(['device_id' => 779105, 'config_location' => $path]);

    $row = $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs?filter[device_id]=779105&fields=id,device_id,command&includeConfig=true')
        ->assertStatus(200)
        ->json('data.0');

    expect(array_keys($row))->toEqualCanonicalizing(['id', 'device_id', 'command', 'config'])
        ->and($row['config'])->toBe("hostname router2\n");

    unlink($path);
});

test('index rejects unknown fields', function () {
    $this->withHeaders(configsApiV2AuthHeader($this->token))
        ->getJson('/api/v2/configs?fields=id,password')
        ->assertStatus(400);
});
