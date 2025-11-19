<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
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

        $response->assertStatus(200);

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

    public function test_update_profile()
    {
        $response = $this->post("/api/user/update-profile/{$this->user->id}", [
            'name' => 'Updated Profile Name',
            'username' => 'newusername',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Profile Name',
            'username' => 'newusername',
        ]);
    }

    public function test_add_external_link()
    {
        $response = $this->post("/api/user/add-external-link/{$this->user->id}", [
            'name' => 'GitHub',
            'url' => 'https://github.com/myprofile',
            'icon' => 'github',
        ]);

        $response->assertStatus(200);
        
        $this->user->refresh();
        $this->assertNotNull($this->user->external_links);
        $this->assertCount(1, $this->user->external_links);
        $this->assertEquals('GitHub', $this->user->external_links[0]['name']);
    }

    public function test_get_external_links()
    {
        $links = [
            ['name' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'github'],
            ['name' => 'Twitter', 'url' => 'https://twitter.com', 'icon' => 'twitter'],
        ];
        
        $this->user->external_links = $links;
        $this->user->save();

        $response = $this->get("/api/user/get-external-links/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertJson($links);
    }

    public function test_change_password_fails_with_incorrect_current_password()
    {
        $this->user->password = Hash::make('oldpassword');
        $this->user->save();

        $response = $this->post("/api/user/{$this->user->id}/change-password", [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Current password is incorrect']);
    }

    public function test_set_socialite_approval_status()
    {
        $user = User::factory()->create(['is_socialite_approved' => 0]);

        $response = $this->post("/api/user/set-socialite-approval-status/{$user->id}", [
            'status' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_socialite_approved' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
