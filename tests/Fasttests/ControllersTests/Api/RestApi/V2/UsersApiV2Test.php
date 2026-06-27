<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi\V2;

use App\Models\RestApiToken;
use App\Models\User;
use Tests\Fasttests\ControllersTests\Api\RestApi\Concerns\TolerantOfTransientDbLocks;
use Tests\TestCase;

class UsersApiV2Test extends TestCase
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

    public function test_index_returns_users(): void
    {
        User::factory(3)->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/users?perPage=50')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_show_returns_user(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v2/users/' . $user->id)
            ->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_store_creates_user(): void
    {
        $email = 'rest-api-v2-user-' . uniqid() . '@example.com';

        $this->withHeaders($this->authHeader())
            ->postJson('/api/v2/users', [
                'name' => 'Rest Api V2 User',
                'username' => 'restapiv2user' . rand(1000, 9999),
                'email' => $email,
                'password' => 'secretpass',
                'repeat_password' => 'secretpass',
                'role' => 'User',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_update_edits_user(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->authHeader())
            ->patchJson('/api/v2/users/' . $user->id, [
                'name' => 'Updated V2 Name',
                'username' => 'updatedv2user' . rand(1000, 9999),
                'email' => $user->email,
                'password' => 'secretpass',
                'repeat_password' => 'secretpass',
                'role' => 'User',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated V2 Name']);
    }

    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJsonTolerant('/api/v2/users/' . $user->id, $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
