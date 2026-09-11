<?php

use App\Models\DeviceCredentials;
use App\Models\RestApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Config;

const CLEARTEXT_PASSWORD = 'SuperSecretDevicePassword99';

const CLEARTEXT_ENABLE_PASSWORD = 'SuperSecretEnablePassword88';

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function deviceCredentialsMaskingAuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

function createCredential(): DeviceCredentials
{
    return DeviceCredentials::factory()->create([
        'cred_name' => 'masking-target',
        'cred_username' => 'netadmin',
        'cred_password' => CLEARTEXT_PASSWORD,
        'cred_enable_password' => CLEARTEXT_ENABLE_PASSWORD,
    ]);
}

test('index does not expose cleartext secrets', function () {
    createCredential();

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials?perPage=50')
        ->assertStatus(200);

    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $response->getContent());
    $this->assertStringNotContainsString(CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
});

test('index returns masked secrets', function () {
    createCredential();

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials?perPage=50')
        ->assertStatus(200);

    $this->assertStringContainsString('*', (string) $response->json('data.0.cred_password'));
    $this->assertStringContainsString('*', (string) $response->json('data.0.cred_enable_password'));
});

test('index still returns identifying fields', function () {
    createCredential();

    $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials?perPage=50')
        ->assertStatus(200)
        ->assertJsonFragment(['cred_name' => 'masking-target'])
        ->assertJsonFragment(['cred_username' => 'netadmin']);
});

test('show does not expose cleartext secrets', function () {
    $cred = createCredential();

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $response->getContent());
    $this->assertStringNotContainsString(CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
});

test('show returns masked secrets', function () {
    $cred = createCredential();

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    $this->assertStringContainsString('*', (string) $response->json('cred_password'));
    $this->assertStringContainsString('*', (string) $response->json('cred_enable_password'));
});

test('masking applies when mask device credentials is disabled', function () {
    Config::set('rConfig.mask_device_credentials', false);
    $cred = createCredential();

    $index = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials?perPage=50')
        ->assertStatus(200);

    $show = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $index->getContent());
    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $show->getContent());
});

test('blank enable password is left untouched', function () {
    $cred = DeviceCredentials::factory()->create([
        'cred_password' => CLEARTEXT_PASSWORD,
        'cred_enable_password' => '',
    ]);

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    // A blank secret decodes to false through EncryptStringCast (pre-existing quirk),
    // so accept every blank-like shape here. Masking must not turn it into stars.
    expect([null, '', 0, '0', false])->toContain($response->json('cred_enable_password'));
});

test('store response does not echo cleartext secrets', function () {
    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->postJson('/api/v1/device-credentials', [
            'cred_name' => 'store-masking-' . uniqid(),
            'cred_description' => 'Created via REST API masking test',
            'cred_username' => 'admin',
            'cred_password' => CLEARTEXT_PASSWORD,
            'cred_enable_password' => CLEARTEXT_ENABLE_PASSWORD,
        ])
        ->assertStatus(200);

    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $response->getContent());
    $this->assertStringNotContainsString(CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
});

test('update response does not echo cleartext secrets', function () {
    $cred = createCredential();

    $response = $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->patchJson('/api/v1/device-credentials/' . $cred->id, [
            'cred_name' => 'update-masking-' . uniqid(),
            'cred_description' => 'updated',
            'cred_username' => 'admin2',
            'cred_password' => CLEARTEXT_PASSWORD,
            'cred_enable_password' => CLEARTEXT_ENABLE_PASSWORD,
            'cred_is_default' => 0,
        ])
        ->assertStatus(200);

    $this->assertStringNotContainsString(CLEARTEXT_PASSWORD, $response->getContent());
    $this->assertStringNotContainsString(CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
});

test('secrets are still stored intact behind the mask', function () {
    $cred = createCredential();

    $this->withHeaders(deviceCredentialsMaskingAuthHeader($this->token))
        ->getJson('/api/v1/device-credentials/' . $cred->id)
        ->assertStatus(200);

    $stored = DeviceCredentials::findOrFail($cred->id);

    expect($stored->cred_password)->toBe(CLEARTEXT_PASSWORD);
    expect($stored->cred_enable_password)->toBe(CLEARTEXT_ENABLE_PASSWORD);
});
