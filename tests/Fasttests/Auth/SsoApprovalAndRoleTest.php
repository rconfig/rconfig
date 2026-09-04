<?php

use App\Models\User;
use App\Services\SocialAuth\SocialAuthHandler;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('an unapproved sso user cannot log in locally', function () {
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

    expect(auth()->check())->toBeFalse('An unapproved SSO account signed in through the local login form, bypassing admin approval.');
    $response->assertRedirect('/login');
});

test('an approved sso user can log in locally', function () {
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
});

test('a local user without the approval flag can still log in', function () {
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

    expect(auth()->check())->toBeTrue('A local account was blocked by the SSO approval check. is_socialite_approved defaults to 0 for local users.');
});

test('a newly provisioned sso account is not an admin', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $user->wasRecentlyCreated = true;

    SocialAuthHandler::assignDefaultRoleOnCreate($user);

    expect($user->fresh()->role)->toBe('User', 'A newly provisioned SSO account was left with an elevated role.');
});

test('an existing admin is not demoted by signing in through sso', function () {
    $user = User::factory()->create(['role' => 'Admin']);
    $user->wasRecentlyCreated = false;

    SocialAuthHandler::assignDefaultRoleOnCreate($user);

    expect($user->fresh()->role)->toBe('Admin', 'An existing Administrator was demoted by an SSO sign in.');
});
