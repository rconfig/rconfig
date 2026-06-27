<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\V2;

use App\Models\RestApiToken;
use App\Models\User;
use Tests\TestCase;

class DashboardHealthApiV2Test extends TestCase
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

    public function test_dashboard_health_latest_returns_200(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/dashboard/health-latest')
            ->assertStatus(200);
    }
}
