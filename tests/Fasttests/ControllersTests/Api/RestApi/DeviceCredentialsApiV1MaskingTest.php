<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\DeviceCredentials;
use App\Models\RestApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Regression cover for cleartext device credential exposure on the external REST API.
 *
 * The token authenticated v1 API must never return usable device passwords. Secrets are
 * masked unconditionally, independent of the MASK_DEVICE_CREDENTIALS setting, which only
 * governs the device endpoints.
 */
class DeviceCredentialsApiV1MaskingTest extends TestCase
{
    protected RestApiToken $token;

    private const CLEARTEXT_PASSWORD = 'SuperSecretDevicePassword99';

    private const CLEARTEXT_ENABLE_PASSWORD = 'SuperSecretEnablePassword88';

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    private function createCredential(): DeviceCredentials
    {
        return DeviceCredentials::factory()->create([
            'cred_name' => 'masking-target',
            'cred_username' => 'netadmin',
            'cred_password' => self::CLEARTEXT_PASSWORD,
            'cred_enable_password' => self::CLEARTEXT_ENABLE_PASSWORD,
        ]);
    }

    public function test_index_does_not_expose_cleartext_secrets(): void
    {
        $this->createCredential();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials?perPage=50')
            ->assertStatus(200);

        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $response->getContent());
        $this->assertStringNotContainsString(self::CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
    }

    public function test_index_returns_masked_secrets(): void
    {
        $this->createCredential();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials?perPage=50')
            ->assertStatus(200);

        $this->assertStringContainsString('*', (string) $response->json('data.0.cred_password'));
        $this->assertStringContainsString('*', (string) $response->json('data.0.cred_enable_password'));
    }

    public function test_index_still_returns_identifying_fields(): void
    {
        $this->createCredential();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials?perPage=50')
            ->assertStatus(200)
            ->assertJsonFragment(['cred_name' => 'masking-target'])
            ->assertJsonFragment(['cred_username' => 'netadmin']);
    }

    public function test_show_does_not_expose_cleartext_secrets(): void
    {
        $cred = $this->createCredential();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $response->getContent());
        $this->assertStringNotContainsString(self::CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
    }

    public function test_show_returns_masked_secrets(): void
    {
        $cred = $this->createCredential();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        $this->assertStringContainsString('*', (string) $response->json('cred_password'));
        $this->assertStringContainsString('*', (string) $response->json('cred_enable_password'));
    }

    public function test_masking_applies_when_mask_device_credentials_is_disabled(): void
    {
        Config::set('rConfig.mask_device_credentials', false);
        $cred = $this->createCredential();

        $index = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials?perPage=50')
            ->assertStatus(200);

        $show = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $index->getContent());
        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $show->getContent());
    }

    public function test_blank_enable_password_is_left_untouched(): void
    {
        $cred = DeviceCredentials::factory()->create([
            'cred_password' => self::CLEARTEXT_PASSWORD,
            'cred_enable_password' => '',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        // A blank secret decodes to false through EncryptStringCast (pre-existing quirk),
        // so accept every blank-like shape here. Masking must not turn it into stars.
        $this->assertContains($response->json('cred_enable_password'), [null, '', 0, '0', false]);
    }

    public function test_store_response_does_not_echo_cleartext_secrets(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/device-credentials', [
                'cred_name' => 'store-masking-' . uniqid(),
                'cred_description' => 'Created via REST API masking test',
                'cred_username' => 'admin',
                'cred_password' => self::CLEARTEXT_PASSWORD,
                'cred_enable_password' => self::CLEARTEXT_ENABLE_PASSWORD,
            ])
            ->assertStatus(200);

        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $response->getContent());
        $this->assertStringNotContainsString(self::CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
    }

    public function test_update_response_does_not_echo_cleartext_secrets(): void
    {
        $cred = $this->createCredential();

        $response = $this->withHeaders($this->authHeader())
            ->patchJson('/api/v1/device-credentials/' . $cred->id, [
                'cred_name' => 'update-masking-' . uniqid(),
                'cred_description' => 'updated',
                'cred_username' => 'admin2',
                'cred_password' => self::CLEARTEXT_PASSWORD,
                'cred_enable_password' => self::CLEARTEXT_ENABLE_PASSWORD,
                'cred_is_default' => 0,
            ])
            ->assertStatus(200);

        $this->assertStringNotContainsString(self::CLEARTEXT_PASSWORD, $response->getContent());
        $this->assertStringNotContainsString(self::CLEARTEXT_ENABLE_PASSWORD, $response->getContent());
    }

    public function test_secrets_are_still_stored_intact_behind_the_mask(): void
    {
        $cred = $this->createCredential();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        $stored = DeviceCredentials::findOrFail($cred->id);

        $this->assertSame(self::CLEARTEXT_PASSWORD, $stored->cred_password);
        $this->assertSame(self::CLEARTEXT_ENABLE_PASSWORD, $stored->cred_enable_password);
    }
}
