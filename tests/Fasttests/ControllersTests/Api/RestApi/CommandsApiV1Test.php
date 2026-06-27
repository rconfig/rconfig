<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Models\Category;
use App\Models\Command;
use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class CommandsApiV1Test extends TestCase
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

    public function test_index_returns_commands(): void
    {
        Command::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/commands?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_command(): void
    {
        $command = Command::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/commands/' . $command->id)
            ->assertStatus(200)
            ->assertJsonFragment(['command' => $command->command]);
    }

    public function test_store_creates_command(): void
    {
        $category = Category::factory()->create();

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/commands', [
                'command' => 'show running-config api-test',
                'description' => 'Created via REST API test',
                'categoryArray' => [$category->id],
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('commands', ['command' => 'show running-config api-test']);
    }

    public function test_store_validation_failure_returns_422(): void
    {
        $this->withHeaders($this->authHeader())
            ->postJson('/api/v1/commands', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['command', 'categoryArray']);
    }

    public function test_update_edits_command(): void
    {
        $category = Category::factory()->create();
        $command = Command::factory()->create();

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v1/commands/' . $command->id, [
                'command' => 'show version api-updated',
                'description' => 'updated',
                'categoryArray' => [$category->id],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('commands', ['id' => $command->id, 'command' => 'show version api-updated']);
    }

    public function test_destroy_deletes_command(): void
    {
        $command = Command::factory()->create();

        $this->deleteJsonTolerant('/api/v1/commands/' . $command->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('commands', ['id' => $command->id]);
    }
}
