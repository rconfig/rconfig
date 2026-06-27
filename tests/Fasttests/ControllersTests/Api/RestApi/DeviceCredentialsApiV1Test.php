<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\DeviceCredentials;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class DeviceCredentialsApiV1Test extends TestCase
{
    use TolerantOfTransientDbLocks;

    protected RestApiToken $token;

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

    public function test_index_returns_credentials(): void
    {
        DeviceCredentials::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_credential(): void
    {
        $cred = DeviceCredentials::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200)
            ->assertJsonFragment(['cred_name' => $cred->cred_name]);
    }

    public function test_store_creates_credential(): void
    {
        $this->withHeaders($this->authHeader())
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
    }

    public function test_update_with_blank_enable_password_stays_blank(): void
    {
        $cred = DeviceCredentials::factory()->create(['cred_enable_password' => '']);

        $this->withHeaders($this->authHeader())
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
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-credentials/' . $cred->id)
            ->assertStatus(200);

        $this->assertContains($response->json('cred_enable_password'), [null, '', 0, '0']);
    }

    public function test_destroy_deletes_credential(): void
    {
        $cred = DeviceCredentials::factory()->create();

        $this->deleteJsonTolerant('/api/v1/device-credentials/' . $cred->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('device_credentials', ['id' => $cred->id]);
    }
}
