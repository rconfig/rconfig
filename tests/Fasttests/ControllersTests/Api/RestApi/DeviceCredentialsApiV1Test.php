<?php

use App\Models\DeviceCredentials;
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
function deviceCredentialsApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns credentials', function () {
    DeviceCredentials::factory(3)->create();

    $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/device-credentials?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns credential', function () {
    $cred = DeviceCredentials::factory()->create();

    $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200)
        ->assertJsonFragment(['cred_name' => $cred->cred_name]);
});

test('store creates credential', function () {
    $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/device-credentials', [
            'cred_name' => 'rest-api-creds-' . uniqid(),
            'cred_description' => 'Created via REST API test',
            'cred_username' => 'admin',
            'cred_password' => 'pass',
            'cred_enable_password' => 'enable',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('device_credentials', ['cred_username' => 'admin']);
});

test('update with blank enable password stays blank', function () {
    $cred = DeviceCredentials::factory()->create(['cred_enable_password' => '']);

    $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/device-credentials/' . $cred->id, [
            'cred_name' => 'updated-cred-name-' . uniqid(),
            'cred_description' => 'updated',
            'cred_username' => 'admin2',
            'cred_password' => 'new-password',
            'cred_enable_password' => null,
            'cred_is_default' => 0,
        ])
        ->assertStatus(200);

    // Blank/disabled enable password stays blank-like after update.
    $response = $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    expect([null, '', 0, '0'])->toContain($response->json('cred_enable_password'));
});

test('destroy deletes credential', function () {
    $cred = DeviceCredentials::factory()->create();

    $this->withHeaders(deviceCredentialsApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('device_credentials', ['id' => $cred->id]);
});
