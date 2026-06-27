<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\User;
use Tests\TestCase;

/**
 * Pro-only API surfaces must not be registered in Core. These should 404 even
 * with a valid token.
 */
class ExcludedRoutesApiTest extends TestCase
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

    public function test_v1_snippets_route_not_registered(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/snippets')
            ->assertStatus(404);
    }

    public function test_v1_device_profiles_route_not_registered(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/device-profiles')
            ->assertStatus(404);
    }

    public function test_v2_policy_compliance_route_not_registered(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/policy-compliance-reports/export')
            ->assertStatus(404);
    }
}
