<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\V2;

use App\Models\Config;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class ConfigsApiV2Test extends TestCase
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

    public function test_index_returns_paginated_configs(): void
    {
        Config::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/configs?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_a_config(): void
    {
        $config = Config::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/configs/' . $config->id)
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $config->id]);
    }

    public function test_destroy_removes_config(): void
    {
        $config = Config::factory()->create([
            'config_location' => 'tests/storage/configs/does-not-exist-' . uniqid() . '.txt',
        ]);

        $this->deleteJsonTolerant('/api/v2/configs/' . $config->id, $this->authHeader())
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('configs', ['id' => $config->id]);
    }

    public function test_search_get_returns_results(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/configs/search?searchTerm=hostname')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_search_post_returns_results(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v2/configs/search', ['searchTerm' => 'hostname'])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_status_count_for_success(): void
    {
        Config::factory()->create(['device_id' => 779001, 'download_status' => 1]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/configs/status/779001/success')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['count']]);
    }

    public function test_status_count_invalid_status_returns_422(): void
    {
        Config::factory()->create(['device_id' => 779002, 'download_status' => 1]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/configs/status/779002/not-a-status')
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Invalid status']);
    }
}
