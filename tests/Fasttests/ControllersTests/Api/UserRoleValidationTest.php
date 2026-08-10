<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression tests for role field validation and privilege checks on user
 * create/update.
 *
 * Enforcement under test:
 *  - app/Http/Requests/StoreUserRequest.php: `role` is validated against a
 *    fixed allowlist, and `authorize()` requires the acting user to already
 *    hold the Admin role before it can be granted to any account, their own
 *    or another's.
 */
class UserRoleValidationTest extends TestCase
{
    protected User $nonAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->nonAdmin = User::factory()->create(['role' => 'User']);
        $this->actingAs($this->nonAdmin);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    /**
     * Claim: no allowlist on `role` + no privilege check on store() lets any
     * authenticated user mint a brand new Admin account.
     */
    public function test_creating_a_user_with_admin_role_requires_existing_admin_privilege(): void
    {
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
    }

    /**
     * Claim: the same missing check applies to update(), letting a user
     * change their own role instead of creating a new account.
     */
    public function test_updating_own_role_to_admin_requires_existing_admin_privilege(): void
    {
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
    }

    /**
     * Claim: StoreUserRequest::authorize() must check privilege, not just
     * whether the caller is logged in.
     */
    public function test_store_user_request_authorize_checks_privilege_for_admin_role(): void
    {
        $request = StoreUserRequest::create('/api/users', 'POST', [
            'role' => 'Admin',
        ]);
        $request->setUserResolver(fn () => $this->nonAdmin);

        $this->assertFalse(
            $request->authorize(),
            'StoreUserRequest::authorize() returned true for a non-admin user attempting to set role=Admin.'
        );
    }

    /**
     * Claim: the `role` validation rule must reject values outside the
     * known set, not just require the field to be present.
     */
    public function test_role_field_only_accepts_known_values(): void
    {
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
        $this->assertArrayHasKey('role', $response->json('errors') ?? []);
    }

    /**
     * Claim (downstream effect): whatever gates Horizon access reads the
     * same role field, so a non-admin must be forbidden and an Admin must
     * be let through.
     */
    public function test_horizon_access_requires_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $nonAdminResponse = $this->actingAs($this->nonAdmin)->get('horizon');
        $nonAdminResponse->assertForbidden();

        $this->app['session']->flush();

        $adminResponse = $this->actingAs($admin)->get('horizon');
        $adminResponse->assertStatus(200);
    }
}
