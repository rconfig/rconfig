<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\User;
use App\Models\Vendor;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class VendorsApiV1Test extends TestCase
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

    public function test_index_returns_vendors(): void
    {
        Vendor::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/vendors?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/vendors/' . $vendor->id)
            ->assertStatus(200)
            ->assertJsonFragment(['vendorName' => $vendor->vendorName]);
    }

    public function test_store_creates_vendor(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/vendors', [
                'vendorName' => 'CiscoApiTest12345',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('vendors', ['vendorName' => 'CiscoApiTest12345']);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/vendors', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['vendorName']);
    }

    public function test_update_edits_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v1/vendors/' . $vendor->id, [
                'vendorName' => 'a-new-vendor-name-api',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'vendorName' => 'a-new-vendor-name-api']);
    }

    public function test_destroy_deletes_vendor(): void
    {
        $vendor = Vendor::factory()->create();

        $this->deleteJsonTolerant('/api/v1/vendors/' . $vendor->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
    }
}
