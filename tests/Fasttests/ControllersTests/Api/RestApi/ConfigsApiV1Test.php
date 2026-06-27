<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\Config;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class ConfigsApiV1Test extends TestCase
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
            ->getJson('/api/v1/configs?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_a_config(): void
    {
        $config = Config::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/' . $config->id)
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $config->id]);
    }

    public function test_destroy_removes_config(): void
    {
        // Point at a path that does not exist so no real file is deleted; the
        // controller still removes the DB row and returns success.
        $config = Config::factory()->create([
            'config_location' => 'tests/storage/configs/does-not-exist-' . uniqid() . '.txt',
        ]);

        $this->deleteJsonTolerant('/api/v1/configs/' . $config->id, $this->authHeader())
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseMissing('configs', ['id' => $config->id]);
    }

    public function test_search_get_returns_results(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/search?searchTerm=hostname')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_search_post_returns_results(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/configs/search', ['searchTerm' => 'hostname'])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_search_validates_minimum_length(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/configs/search', ['searchTerm' => 'ab'])
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_status_count_for_success(): void
    {
        Config::factory()->create(['device_id' => 778001, 'download_status' => 1]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/status/778001/success')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['count']]);
    }

    public function test_status_count_for_failed(): void
    {
        Config::factory()->create(['device_id' => 778002, 'download_status' => 0]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/status/778002/failed')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['count']]);
    }

    public function test_status_count_for_all(): void
    {
        Config::factory()->create(['device_id' => 778003, 'download_status' => 1]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/status/778003/all')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['count']]);
    }

    public function test_status_count_with_invalid_status_returns_422(): void
    {
        Config::factory()->create(['device_id' => 778004, 'download_status' => 1]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/status/778004/not-a-status')
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Invalid status']);
    }

    public function test_all_by_device_id_returns_configs(): void
    {
        Config::factory(2)->create([
            'device_id' => 778005,
            'download_status' => 1,
            'config_location' => 'tests/storage/configs/missing-' . uniqid() . '.txt',
        ]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/all-by-deviceid/778005')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_all_by_device_id_unknown_device_returns_failure(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configs/all-by-deviceid/999777555')
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }
}
