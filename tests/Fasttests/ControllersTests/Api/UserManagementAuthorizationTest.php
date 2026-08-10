<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Cover for the Admin boundary around user administration.
 *
 * The /api/users resource acts on arbitrary accounts, so it is Admin only. Account
 * holders manage themselves through the profile endpoints, which resolve the account
 * from the session and ignore any id in the URL.
 *
 * The case that matters most: a standard user must not be able to reset an Admin's
 * password. Checking only the role being written is not enough, because an attacker
 * takes over an existing Admin rather than promoting themselves.
 */
class UserManagementAuthorizationTest extends TestCase
{
    protected User $admin;
    protected User $standardUser;

    private const ADMIN_PASSWORD = 'OriginalAdminPassword1';

    private const STANDARD_PASSWORD = 'OriginalUserPassword1';

    private const ATTACKER_PASSWORD = 'AttackerChosenPassword1';

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->admin = User::factory()->create([
            'role' => 'Admin',
            'password' => Hash::make(self::ADMIN_PASSWORD),
        ]);

        $this->standardUser = User::factory()->create([
            'role' => 'User',
            'password' => Hash::make(self::STANDARD_PASSWORD),
        ]);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    /**
     * @return array<string, string>
     */
    private function userPayload(string $email, string $role = 'User', string $password = self::ATTACKER_PASSWORD): array
    {
        return [
            'name' => 'Payload User',
            'email' => $email,
            'password' => $password,
            'repeat_password' => $password,
            'role' => $role,
        ];
    }

    /* The standard user is refused across the whole administration surface. */

    public function test_a_standard_user_cannot_list_users(): void
    {
        $this->actingAs($this->standardUser)->getJson('/api/users')->assertStatus(403);
    }

    public function test_a_standard_user_cannot_view_another_user(): void
    {
        $this->actingAs($this->standardUser)
            ->getJson('/api/users/' . $this->admin->id)
            ->assertStatus(403);
    }

    public function test_a_standard_user_cannot_create_a_user(): void
    {
        $this->actingAs($this->standardUser)
            ->postJson('/api/users', $this->userPayload('created_by_standard@example.test'))
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'created_by_standard@example.test']);
    }

    public function test_a_standard_user_cannot_create_an_admin(): void
    {
        $this->actingAs($this->standardUser)
            ->postJson('/api/users', $this->userPayload('new_admin@example.test', 'Admin'))
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'new_admin@example.test']);
    }

    /**
     * The original escalation: reset an Admin's password while sending role 'User',
     * then sign in as that Admin.
     */
    public function test_a_standard_user_cannot_reset_an_admin_password(): void
    {
        $response = $this->actingAs($this->standardUser)->patchJson(
            '/api/users/' . $this->admin->id,
            $this->userPayload($this->admin->email, 'User')
        );

        $response->assertStatus(403);

        $this->admin->refresh();
        $this->assertTrue(
            Hash::check(self::ADMIN_PASSWORD, $this->admin->password),
            'A standard user changed an admin password.'
        );
        $this->assertSame('Admin', $this->admin->role, 'A standard user demoted an admin.');
    }

    public function test_a_standard_user_cannot_promote_themselves_to_admin(): void
    {
        $this->actingAs($this->standardUser)->patchJson(
            '/api/users/' . $this->standardUser->id,
            $this->userPayload($this->standardUser->email, 'Admin')
        )->assertStatus(403);

        $this->standardUser->refresh();
        $this->assertSame('User', $this->standardUser->role);
    }

    public function test_a_standard_user_cannot_edit_their_own_record_through_the_admin_endpoint(): void
    {
        $this->actingAs($this->standardUser)->patchJson(
            '/api/users/' . $this->standardUser->id,
            $this->userPayload($this->standardUser->email, 'User')
        )->assertStatus(403);

        $this->standardUser->refresh();
        $this->assertTrue(Hash::check(self::STANDARD_PASSWORD, $this->standardUser->password));
    }

    public function test_a_standard_user_cannot_delete_a_user(): void
    {
        $this->actingAs($this->standardUser)
            ->deleteJson('/api/users/' . $this->admin->id)
            ->assertStatus(403);

        $this->assertNotNull(User::find($this->admin->id), 'A standard user deleted an admin.');
    }

    public function test_a_standard_user_cannot_delete_many_users(): void
    {
        $this->actingAs($this->standardUser)
            ->postJson('/api/users/delete-many', ['ids' => [$this->admin->id]])
            ->assertStatus(403);

        $this->assertNotNull(User::find($this->admin->id));
    }

    /* An admin retains the full administration surface. */

    public function test_an_admin_can_list_users(): void
    {
        $this->actingAs($this->admin)->getJson('/api/users')->assertSuccessful();
    }

    public function test_an_admin_can_create_a_user(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', $this->userPayload('created_by_admin@example.test'))
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'created_by_admin@example.test']);
    }

    public function test_an_admin_can_create_another_admin(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', $this->userPayload('second_admin@example.test', 'Admin'))
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['email' => 'second_admin@example.test', 'role' => 'Admin']);
    }

    /**
     * An admin resetting somebody else's password is not asked for the existing one.
     */
    public function test_an_admin_can_reset_another_users_password_without_the_current_password(): void
    {
        $this->actingAs($this->admin)->patchJson(
            '/api/users/' . $this->standardUser->id,
            $this->userPayload($this->standardUser->email, 'User', 'AdminSetPassword1')
        )->assertSuccessful();

        $this->standardUser->refresh();
        $this->assertTrue(Hash::check('AdminSetPassword1', $this->standardUser->password));
    }

    public function test_an_admin_can_change_a_users_role(): void
    {
        $this->actingAs($this->admin)->patchJson(
            '/api/users/' . $this->standardUser->id,
            $this->userPayload($this->standardUser->email, 'Admin')
        )->assertSuccessful();

        $this->standardUser->refresh();
        $this->assertSame('Admin', $this->standardUser->role);
    }

    public function test_an_admin_can_delete_a_user(): void
    {
        $target = User::factory()->create(['role' => 'User']);

        $this->actingAs($this->admin)
            ->deleteJson('/api/users/' . $target->id)
            ->assertSuccessful();

        $this->assertNull(User::find($target->id));
    }

    /* SSO approval decides whether an account can sign in, so it is admin only. */

    public function test_a_standard_user_cannot_approve_an_sso_account(): void
    {
        $pending = User::factory()->create(['role' => 'User', 'is_socialite_approved' => 0]);

        $this->actingAs($this->standardUser)
            ->postJson('/api/user/set-socialite-approval-status/' . $pending->id, ['status' => 1])
            ->assertStatus(403);

        $pending->refresh();
        $this->assertEquals(0, $pending->is_socialite_approved);
    }

    public function test_an_admin_can_approve_an_sso_account(): void
    {
        $pending = User::factory()->create(['role' => 'User', 'is_socialite_approved' => 0]);

        $this->actingAs($this->admin)
            ->postJson('/api/user/set-socialite-approval-status/' . $pending->id, ['status' => 1])
            ->assertSuccessful();

        $pending->refresh();
        $this->assertEquals(1, $pending->is_socialite_approved);
    }

    /* Self service endpoints act on the caller, whatever id is in the URL. */

    public function test_setting_notification_status_only_affects_the_caller(): void
    {
        $this->admin->get_notifications = true;
        $this->admin->save();

        $this->actingAs($this->standardUser)
            ->postJson('/api/user/set-notification-status/' . $this->admin->id, ['status' => false])
            ->assertSuccessful();

        $this->admin->refresh();
        $this->standardUser->refresh();

        $this->assertTrue($this->admin->get_notifications, 'Another account was modified.');
        $this->assertFalse($this->standardUser->get_notifications);
    }

    public function test_setting_locale_only_affects_the_caller(): void
    {
        $this->admin->locale = 'en';
        $this->admin->save();

        $this->actingAs($this->standardUser)->postJson(
            '/api/user/' . $this->admin->id . '/setLocale',
            ['locale' => 'fr', 'datestyle' => 'd/m/Y', 'timestyle' => 'H:i']
        )->assertSuccessful();

        $this->admin->refresh();
        $this->standardUser->refresh();

        $this->assertSame('en', $this->admin->locale, 'Another account was modified.');
        $this->assertSame('fr', $this->standardUser->locale);
    }

    public function test_changing_a_password_requires_the_correct_current_password(): void
    {
        $this->actingAs($this->standardUser)->postJson(
            '/api/user/' . $this->standardUser->id . '/change-password',
            [
                'current_password' => 'NotTheRightPassword1',
                'new_password' => self::ATTACKER_PASSWORD,
                'new_password_confirmation' => self::ATTACKER_PASSWORD,
            ]
        )->assertStatus(422);

        $this->standardUser->refresh();
        $this->assertTrue(Hash::check(self::STANDARD_PASSWORD, $this->standardUser->password));
    }

    public function test_changing_a_password_with_another_users_id_only_changes_the_caller(): void
    {
        $this->actingAs($this->standardUser)->postJson(
            '/api/user/' . $this->admin->id . '/change-password',
            [
                'current_password' => self::STANDARD_PASSWORD,
                'new_password' => 'MyOwnNewPassword1',
                'new_password_confirmation' => 'MyOwnNewPassword1',
            ]
        )->assertSuccessful();

        $this->admin->refresh();
        $this->standardUser->refresh();

        $this->assertTrue(
            Hash::check(self::ADMIN_PASSWORD, $this->admin->password),
            'The admin password was changed through another account.'
        );
        $this->assertTrue(Hash::check('MyOwnNewPassword1', $this->standardUser->password));
    }

    public function test_a_standard_user_can_read_their_own_profile(): void
    {
        $response = $this->actingAs($this->standardUser)->getJson('/api/user/profile');

        $response->assertSuccessful();
        $response->assertJsonPath('id', $this->standardUser->id);
        $response->assertJsonMissingPath('password');
    }

    public function test_the_profile_endpoint_ignores_any_other_account(): void
    {
        $response = $this->actingAs($this->standardUser)->getJson('/api/user/profile');

        $response->assertJsonPath('email', $this->standardUser->email);
        $this->assertStringNotContainsString($this->admin->email, $response->getContent());
    }

    public function test_external_links_are_returned_for_the_caller_only(): void
    {
        $this->admin->external_links = [['name' => 'admin link', 'url' => 'https://example.test', 'icon' => 'x']];
        $this->admin->save();

        $response = $this->actingAs($this->standardUser)
            ->getJson('/api/user/get-external-links/' . $this->admin->id);

        $response->assertSuccessful();
        $this->assertStringNotContainsString('admin link', $response->getContent());
    }
}
