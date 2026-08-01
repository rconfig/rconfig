<?php

namespace Tests\Fasttests\Auth;

use App\Models\User;
use App\Services\SocialAuth\SocialAuthHandler;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regression tests for two gaps found while auditing the self-registration
 * report of 2026-08-01.
 *
 *  1. SocialiteController gated SSO sign in on is_socialite_approved, but the
 *     local login path did not. An unapproved SSO account could set a password
 *     through the standard reset flow and sign in, skipping approval.
 *  2. None of the SSO provider services set `role`, so newly provisioned SSO
 *     accounts inherited the users.role column default, which was 'Admin'.
 */
class SsoApprovalAndRoleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_an_unapproved_sso_user_cannot_log_in_locally(): void
    {
        User::factory()->create([
            'email' => 'unapproved-sso@example.test',
            'username' => 'unapprovedsso',
            'password' => Hash::make('KnownPassword1!'),
            'is_socialite' => true,
            'is_socialite_approved' => false,
            'role' => 'User',
        ]);

        $response = $this->post('/login', [
            'username' => 'unapproved-sso@example.test',
            'password' => 'KnownPassword1!',
        ]);

        $this->assertFalse(
            auth()->check(),
            'An unapproved SSO account signed in through the local login form, bypassing admin approval.'
        );
        $response->assertRedirect('/login');
    }

    public function test_an_approved_sso_user_can_log_in_locally(): void
    {
        User::factory()->create([
            'email' => 'approved-sso@example.test',
            'username' => 'approvedsso',
            'password' => Hash::make('KnownPassword1!'),
            'is_socialite' => true,
            'is_socialite_approved' => true,
            'role' => 'User',
        ]);

        $this->post('/login', [
            'username' => 'approved-sso@example.test',
            'password' => 'KnownPassword1!',
        ]);

        $this->assertAuthenticated();
    }

    /**
     * The approval flag defaults to 0 and only ever gets set for SSO accounts,
     * so the new check must not lock out ordinary local accounts.
     */
    public function test_a_local_user_without_the_approval_flag_can_still_log_in(): void
    {
        User::factory()->create([
            'email' => 'plain-local@example.test',
            'username' => 'plainlocal',
            'password' => Hash::make('KnownPassword1!'),
            'is_socialite' => false,
            'is_socialite_approved' => false,
            'role' => 'User',
        ]);

        $this->post('/login', [
            'username' => 'plain-local@example.test',
            'password' => 'KnownPassword1!',
        ]);

        $this->assertTrue(
            auth()->check(),
            'A local account was blocked by the SSO approval check. is_socialite_approved defaults to 0 for local users.'
        );
    }

    public function test_a_newly_provisioned_sso_account_is_not_an_admin(): void
    {
        $user = User::factory()->create(['role' => 'Admin']);
        $user->wasRecentlyCreated = true;

        SocialAuthHandler::assignDefaultRoleOnCreate($user);

        $this->assertSame('User', $user->fresh()->role, 'A newly provisioned SSO account was left with an elevated role.');
    }

    /**
     * An existing Administrator signing in through SSO must keep their role.
     */
    public function test_an_existing_admin_is_not_demoted_by_signing_in_through_sso(): void
    {
        $user = User::factory()->create(['role' => 'Admin']);
        $user->wasRecentlyCreated = false;

        SocialAuthHandler::assignDefaultRoleOnCreate($user);

        $this->assertSame('Admin', $user->fresh()->role, 'An existing Administrator was demoted by an SSO sign in.');
    }
}
