<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_a_user_requires_a_name()
    {
        $response = $this->json('post', '/api/users', ['name' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('name', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_show_single_user()
    {
        $user = User::factory()->create();
        $response = $this->get('/api/users/' . $user->id);

        $response->assertJson(['name' => $user->name]);
    }

    public function test_get_all_users()
    {
        $user = User::factory(10)->create();
        $response = $this->get('/api/users?page=1&perPage=100');
        $this->assertGreaterThan(9, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_create_user()
    {
        $user = User::factory()->create();
        $this->post('/api/users', $user->toArray());

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
        ]);
    }

    public function test_create_user_with_user_role()
    {
        $user = User::factory()->create([
            'role' => 'User',
        ]);
        $this->post('/api/users', $user->toArray());

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => 'User',
        ]);
    }

    public function test_create_user_with_blank_username()
    {
        $user = User::factory()->create([
            'username' => null,
        ]);
        $this->post('/api/users', $user->toArray());

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'username' => null,
        ]);
    }

    public function test_edit_user()
    {
        $user = User::factory()->create();

        $response = $this->patch('/api/users/' . $user->id, [
            'name' => 'a new tag name',
            'username' => 'username111',
            'email' => 'me@example.com',
            'password' => 'secretpass',
            'repeat_password' => 'secretpass',
            'role' => 'User',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'a new tag name',
            'username' => 'username111',
            'email' => 'me@example.com',
            'role' => 'User',
        ]);
    }

    public function test_delete_user()
    {
        $user = User::factory()->create();

        $this->delete('/api/users/' . $user->id);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}
