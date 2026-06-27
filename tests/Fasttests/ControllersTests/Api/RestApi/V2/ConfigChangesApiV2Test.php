<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\V2;

use App\Models\ConfigChange;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\TestCase;

class ConfigChangesApiV2Test extends TestCase
{
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

    public function test_show_existing_change(): void
    {
        $change = ConfigChange::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/config-changes/' . $change->id)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_show_missing_change_returns_404(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/config-changes/99999999')
            ->assertStatus(404)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_by_config_existing_returns_change(): void
    {
        $change = ConfigChange::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/config-changes/by-config/' . $change->current_config_id)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_by_config_missing_returns_404(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/config-changes/by-config/99999999')
            ->assertStatus(404)
            ->assertJsonFragment(['success' => false]);
    }
}
