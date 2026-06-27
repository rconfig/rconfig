<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\User;
use Tests\TestCase;

class RestApiAuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        // Ensure a system user exists for the token middleware to act as.
        User::factory()->create();
    }

    public function test_missing_token_returns_401(): void
    {
        $this->getJson('/api/v1/apitest')->assertStatus(401);
        $this->getJson('/api/v2/apitest')->assertStatus(401);
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->withHeaders(['apitoken' => 'not-a-real-token'])
            ->getJson('/api/v1/apitest')
            ->assertStatus(401);
    }

    public function test_valid_token_in_header_returns_200(): void
    {
        $token = RestApiToken::factory()->create();

        $this->withHeaders(['apitoken' => $token->api_token])
            ->getJson('/api/v1/apitest')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_valid_token_in_query_returns_200(): void
    {
        $token = RestApiToken::factory()->create();

        $this->getJson('/api/v2/apitest?apitoken=' . $token->api_token)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_legacy_api_token_param_returns_422(): void
    {
        $token = RestApiToken::factory()->create();

        $this->getJson('/api/v1/apitest?api_token=' . $token->api_token)
            ->assertStatus(422);
    }
}
