<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->beginTransaction();

    // User administration is Admin only, so the acting user needs that role to
    // exercise the CRUD surface here. The Admin boundary itself is covered in
    // UserManagementAuthorizationTest.
    $this->user = User::factory()->create(['role' => 'Admin']);
    $this->actingAs($this->user);
});

test('a user requires a name', function () {
    $response = $this->json('post', '/api/users', ['name' => null]);

    $response->assertJson(['errors' => true]);
    expect($response['errors'])->toHaveKey('name');
    $response->assertStatus(422);
});

test('show single user', function () {
    $user = User::factory()->create();
    $response = $this->get('/api/users/' . $user->id);

    $response->assertJson(['name' => $user->name]);
});

test('get all users', function () {
    $user = User::factory(10)->create();
    $response = $this->get('/api/users?page=1&perPage=100');
    expect(count($response['data']))->toBeGreaterThan(9);
    $response->assertStatus(200);
});

test('create user', function () {
    $user = User::factory()->create();
    $this->post('/api/users', $user->toArray());

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
    ]);
});

test('create user with user role', function () {
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
});

test('create user with blank username', function () {
    $user = User::factory()->create([
        'username' => null,
    ]);
    $this->post('/api/users', $user->toArray());

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
        'username' => null,
    ]);
});

test('edit user', function () {
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
});

test('delete user', function () {
    $user = User::factory()->create();

    $this->delete('/api/users/' . $user->id);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('update profile', function () {
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
});

test('add external link', function () {
    $response = $this->post("/api/user/add-external-link/{$this->user->id}", [
        'name' => 'GitHub',
        'url' => 'https://github.com/myprofile',
        'icon' => 'github',
    ]);

    $response->assertStatus(200);

    $this->user->refresh();
    expect($this->user->external_links)->not->toBeNull();
    expect($this->user->external_links)->toHaveCount(1);
    expect($this->user->external_links[0]['name'])->toEqual('GitHub');
});

test('get external links', function () {
    $links = [
        ['name' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'github'],
        ['name' => 'Twitter', 'url' => 'https://twitter.com', 'icon' => 'twitter'],
    ];

    $this->user->external_links = $links;
    $this->user->save();

    $response = $this->get("/api/user/get-external-links/{$this->user->id}");

    $response->assertStatus(200);
    $response->assertJson($links);
});

test('change password fails with incorrect current password', function () {
    $this->user->password = Hash::make('oldpassword');
    $this->user->save();

    $response = $this->post("/api/user/{$this->user->id}/change-password", [
        'current_password' => 'wrongpassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Current password is incorrect']);
});

test('set socialite approval status', function () {
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
});

afterEach(function () {
    $this->rollBackTransaction();
});
