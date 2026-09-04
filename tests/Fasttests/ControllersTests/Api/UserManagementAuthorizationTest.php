<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

const ADMIN_PASSWORD = 'OriginalAdminPassword1';

const STANDARD_PASSWORD = 'OriginalUserPassword1';

const ATTACKER_PASSWORD = 'AttackerChosenPassword1';

beforeEach(function () {
    $this->beginTransaction();

    $this->admin = User::factory()->create([
        'role' => 'Admin',
        'password' => Hash::make(ADMIN_PASSWORD),
    ]);

    $this->standardUser = User::factory()->create([
        'role' => 'User',
        'password' => Hash::make(STANDARD_PASSWORD),
    ]);
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * @return array<string, string>
 */
function userPayload(string $email, string $role = 'User', string $password = ATTACKER_PASSWORD): array
{
    return [
        'name' => 'Payload User',
        'email' => $email,
        'password' => $password,
        'repeat_password' => $password,
        'role' => $role,
    ];
}

test('a standard user cannot list users', function () {
    $this->actingAs($this->standardUser)->getJson('/api/users')->assertStatus(403);
});

test('a standard user cannot view another user', function () {
    $this->actingAs($this->standardUser)
        ->getJson('/api/users/' . $this->admin->id)
        ->assertStatus(403);
});

test('a standard user cannot create a user', function () {
    $this->actingAs($this->standardUser)
        ->postJson('/api/users', userPayload('created_by_standard@example.test'))
        ->assertStatus(403);

    $this->assertDatabaseMissing('users', ['email' => 'created_by_standard@example.test']);
});

test('a standard user cannot create an admin', function () {
    $this->actingAs($this->standardUser)
        ->postJson('/api/users', userPayload('new_admin@example.test', 'Admin'))
        ->assertStatus(403);

    $this->assertDatabaseMissing('users', ['email' => 'new_admin@example.test']);
});

test('a standard user cannot reset an admin password', function () {
    $response = $this->actingAs($this->standardUser)->patchJson(
        '/api/users/' . $this->admin->id,
        userPayload($this->admin->email, 'User')
    );

    $response->assertStatus(403);

    $this->admin->refresh();
    expect(Hash::check(ADMIN_PASSWORD, $this->admin->password))->toBeTrue('A standard user changed an admin password.');
    expect($this->admin->role)->toBe('Admin', 'A standard user demoted an admin.');
});

test('a standard user cannot promote themselves to admin', function () {
    $this->actingAs($this->standardUser)->patchJson(
        '/api/users/' . $this->standardUser->id,
        userPayload($this->standardUser->email, 'Admin')
    )->assertStatus(403);

    $this->standardUser->refresh();
    expect($this->standardUser->role)->toBe('User');
});

test('a standard user cannot edit their own record through the admin endpoint', function () {
    $this->actingAs($this->standardUser)->patchJson(
        '/api/users/' . $this->standardUser->id,
        userPayload($this->standardUser->email, 'User')
    )->assertStatus(403);

    $this->standardUser->refresh();
    expect(Hash::check(STANDARD_PASSWORD, $this->standardUser->password))->toBeTrue();
});

test('a standard user cannot delete a user', function () {
    $this->actingAs($this->standardUser)
        ->deleteJson('/api/users/' . $this->admin->id)
        ->assertStatus(403);

    expect(User::find($this->admin->id))->not->toBeNull('A standard user deleted an admin.');
});

test('a standard user cannot delete many users', function () {
    $this->actingAs($this->standardUser)
        ->postJson('/api/users/delete-many', ['ids' => [$this->admin->id]])
        ->assertStatus(403);

    expect(User::find($this->admin->id))->not->toBeNull();
});

test('an admin can list users', function () {
    $this->actingAs($this->admin)->getJson('/api/users')->assertSuccessful();
});

test('an admin can create a user', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/users', userPayload('created_by_admin@example.test'))
        ->assertSuccessful();

    $this->assertDatabaseHas('users', ['email' => 'created_by_admin@example.test']);
});

test('an admin can create another admin', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/users', userPayload('second_admin@example.test', 'Admin'))
        ->assertSuccessful();

    $this->assertDatabaseHas('users', ['email' => 'second_admin@example.test', 'role' => 'Admin']);
});

test('an admin can reset another users password without the current password', function () {
    $this->actingAs($this->admin)->patchJson(
        '/api/users/' . $this->standardUser->id,
        userPayload($this->standardUser->email, 'User', 'AdminSetPassword1')
    )->assertSuccessful();

    $this->standardUser->refresh();
    expect(Hash::check('AdminSetPassword1', $this->standardUser->password))->toBeTrue();
});

test('an admin can change a users role', function () {
    $this->actingAs($this->admin)->patchJson(
        '/api/users/' . $this->standardUser->id,
        userPayload($this->standardUser->email, 'Admin')
    )->assertSuccessful();

    $this->standardUser->refresh();
    expect($this->standardUser->role)->toBe('Admin');
});

test('an admin can delete a user', function () {
    $target = User::factory()->create(['role' => 'User']);

    $this->actingAs($this->admin)
        ->deleteJson('/api/users/' . $target->id)
        ->assertSuccessful();

    expect(User::find($target->id))->toBeNull();
});

test('a standard user cannot approve an sso account', function () {
    $pending = User::factory()->create(['role' => 'User', 'is_socialite_approved' => 0]);

    $this->actingAs($this->standardUser)
        ->postJson('/api/user/set-socialite-approval-status/' . $pending->id, ['status' => 1])
        ->assertStatus(403);

    $pending->refresh();
    expect($pending->is_socialite_approved)->toEqual(0);
});

test('an admin can approve an sso account', function () {
    $pending = User::factory()->create(['role' => 'User', 'is_socialite_approved' => 0]);

    $this->actingAs($this->admin)
        ->postJson('/api/user/set-socialite-approval-status/' . $pending->id, ['status' => 1])
        ->assertSuccessful();

    $pending->refresh();
    expect($pending->is_socialite_approved)->toEqual(1);
});

test('setting notification status only affects the caller', function () {
    $this->admin->get_notifications = true;
    $this->admin->save();

    $this->actingAs($this->standardUser)
        ->postJson('/api/user/set-notification-status/' . $this->admin->id, ['status' => false])
        ->assertSuccessful();

    $this->admin->refresh();
    $this->standardUser->refresh();

    expect($this->admin->get_notifications)->toBeTrue('Another account was modified.');
    expect($this->standardUser->get_notifications)->toBeFalse();
});

test('setting locale only affects the caller', function () {
    $this->admin->locale = 'en';
    $this->admin->save();

    $this->actingAs($this->standardUser)->postJson(
        '/api/user/' . $this->admin->id . '/setLocale',
        ['locale' => 'fr', 'datestyle' => 'd/m/Y', 'timestyle' => 'H:i']
    )->assertSuccessful();

    $this->admin->refresh();
    $this->standardUser->refresh();

    expect($this->admin->locale)->toBe('en', 'Another account was modified.');
    expect($this->standardUser->locale)->toBe('fr');
});

test('changing a password requires the correct current password', function () {
    $this->actingAs($this->standardUser)->postJson(
        '/api/user/' . $this->standardUser->id . '/change-password',
        [
            'current_password' => 'NotTheRightPassword1',
            'new_password' => ATTACKER_PASSWORD,
            'new_password_confirmation' => ATTACKER_PASSWORD,
        ]
    )->assertStatus(422);

    $this->standardUser->refresh();
    expect(Hash::check(STANDARD_PASSWORD, $this->standardUser->password))->toBeTrue();
});

test('changing a password with another users id only changes the caller', function () {
    $this->actingAs($this->standardUser)->postJson(
        '/api/user/' . $this->admin->id . '/change-password',
        [
            'current_password' => STANDARD_PASSWORD,
            'new_password' => 'MyOwnNewPassword1',
            'new_password_confirmation' => 'MyOwnNewPassword1',
        ]
    )->assertSuccessful();

    $this->admin->refresh();
    $this->standardUser->refresh();

    expect(Hash::check(ADMIN_PASSWORD, $this->admin->password))->toBeTrue('The admin password was changed through another account.');
    expect(Hash::check('MyOwnNewPassword1', $this->standardUser->password))->toBeTrue();
});

test('a standard user can read their own profile', function () {
    $response = $this->actingAs($this->standardUser)->getJson('/api/user/profile');

    $response->assertSuccessful();
    $response->assertJsonPath('id', $this->standardUser->id);
    $response->assertJsonMissingPath('password');
});

test('the profile endpoint ignores any other account', function () {
    $response = $this->actingAs($this->standardUser)->getJson('/api/user/profile');

    $response->assertJsonPath('email', $this->standardUser->email);
    $this->assertStringNotContainsString($this->admin->email, $response->getContent());
});

test('external links are returned for the caller only', function () {
    $this->admin->external_links = [['name' => 'admin link', 'url' => 'https://example.test', 'icon' => 'x']];
    $this->admin->save();

    $response = $this->actingAs($this->standardUser)
        ->getJson('/api/user/get-external-links/' . $this->admin->id);

    $response->assertSuccessful();
    $this->assertStringNotContainsString('admin link', $response->getContent());
});
