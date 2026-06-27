<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\RestApiToken;
use App\Models\User;
use Tests\TestCase;

class RestApiTokenControllerTest extends TestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_create_returns_plaintext_token_once(): void
    {
        $response = $this->postJson('/api/settings/rest-api-token', [
            'api_token_name' => 'my-automation',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'api_token_name', 'api_token']]);

        $plaintext = $response->json('data.api_token');
        $this->assertNotEmpty($plaintext);
        $this->assertDatabaseHas('rest_api_tokens', ['api_token_name' => 'my-automation']);
    }

    public function test_index_never_exposes_the_raw_token(): void
    {
        $token = RestApiToken::factory()->create(['api_token_name' => 'listed-token']);

        $response = $this->getJson('/api/settings/rest-api-token');

        $response->assertStatus(200)
            ->assertJsonFragment(['api_token_name' => 'listed-token']);

        $payload = $response->json('data');
        foreach ($payload as $row) {
            $this->assertArrayNotHasKey('api_token', $row);
        }

        $this->assertStringNotContainsString($token->api_token, $response->getContent());
    }

    public function test_duplicate_name_is_rejected(): void
    {
        RestApiToken::factory()->create(['api_token_name' => 'dupe']);

        $this->postJson('/api/settings/rest-api-token', ['api_token_name' => 'dupe'])
            ->assertStatus(422);
    }

    public function test_delete_removes_token(): void
    {
        $token = RestApiToken::factory()->create();

        $this->deleteJson('/api/settings/rest-api-token/' . $token->id)
            ->assertStatus(200);

        $this->assertDatabaseMissing('rest_api_tokens', ['id' => $token->id]);
    }
}
