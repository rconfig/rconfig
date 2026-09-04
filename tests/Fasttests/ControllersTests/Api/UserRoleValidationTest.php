<?php

use App\Http\Requests\StoreUserRequest;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();
    $this->nonAdmin = User::factory()->create(['role' => 'User']);
    $this->actingAs($this->nonAdmin);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('creating a user with admin role requires existing admin privilege', function () {
    $response = $this->postJson('/api/users', [
        'name' => 'Attacker Created Admin',
        'email' => 'pwned-admin@example.com',
        'username' => 'pwnedadmin',
        'password' => 'password1',
        'repeat_password' => 'password1',
        'role' => 'Admin',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('users', [
        'email' => 'pwned-admin@example.com',
        'role' => 'Admin',
    ]);
});

test('updating own role to admin requires existing admin privilege', function () {
    $response = $this->patchJson('/api/users/' . $this->nonAdmin->id, [
        'name' => $this->nonAdmin->name,
        'email' => $this->nonAdmin->email,
        'password' => 'password1',
        'repeat_password' => 'password1',
        'role' => 'Admin',
    ]);

    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'id' => $this->nonAdmin->id,
        'role' => 'User',
    ]);
});

test('store user request authorize checks privilege for admin role', function () {
    $request = StoreUserRequest::create('/api/users', 'POST', [
        'role' => 'Admin',
    ]);
    $request->setUserResolver(fn () => $this->nonAdmin);

    expect($request->authorize())->toBeFalse('StoreUserRequest::authorize() returned true for a non-admin user attempting to set role=Admin.');
});

test('role field only accepts known values', function () {
    // Act as an Admin: a standard user is now refused before validation runs,
    // so the allowlist would never be reached.
    $this->actingAs(User::factory()->create(['role' => 'Admin']));

    $response = $this->postJson('/api/users', [
        'name' => 'Garbage Role User',
        'email' => 'garbage-role@example.com',
        'username' => 'garbagerole',
        'password' => 'password1',
        'repeat_password' => 'password1',
        'role' => 'TotallyBogusRoleThatDoesNotExist12345',
    ]);

    $response->assertStatus(422);
    expect($response->json('errors') ?? [])->toHaveKey('role');
});

test('horizon access requires admin role', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    $nonAdminResponse = $this->actingAs($this->nonAdmin)->get('horizon');
    $nonAdminResponse->assertForbidden();

    $this->app['session']->flush();

    $adminResponse = $this->actingAs($admin)->get('horizon');
    $adminResponse->assertStatus(200);
});
