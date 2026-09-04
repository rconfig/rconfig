<?php

// https://github.com/DCzajkowski/auth-tests
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function getValidToken($user)
{
    return Password::broker()->createToken($user);
}

function getInvalidToken()
{
    return 'invalid-token';
}

function passwordResetGetRoute($token)
{
    return route('password.request', $token);
}

function passwordResetPostRoute()
{
    return '/password/reset';
}

function successfulPasswordResetRoute()
{
    return 'dashboard';
}

test('user can view a password reset form', function () {
    $user = User::factory()->create();

    $response = $this->get(passwordResetGetRoute($token = getValidToken($user)));

    $response->assertSuccessful();
    $response->assertViewIs('auth.passwords.email');
    // $response->assertViewHas('token', $token);
});

test('user can reset password with valid token', function () {
    Event::fake();
    $user = User::factory()->create();

    $response = $this->post(passwordResetPostRoute(), [
        'token' => getValidToken($user),
        'email' => $user->email,
        'password' => 'new-awesome-password',
        'password_confirmation' => 'new-awesome-password',
    ]);

    $response->assertRedirect(successfulPasswordResetRoute());
    expect($user->fresh()->email)->toEqual($user->email);
    $this->assertAuthenticatedAs($user);
    Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
        return $e->user->id === $user->id;
    });
});

test('user cannot reset password with invalid token', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->from(passwordResetGetRoute(getInvalidToken()))->post(passwordResetPostRoute(), [
        'token' => getInvalidToken(),
        'email' => $user->email,
        'password' => 'new-awesome-password',
        'password_confirmation' => 'new-awesome-password',
    ]);

    $response->assertRedirect(passwordResetGetRoute(getInvalidToken()));
    expect($user->fresh()->email)->toEqual($user->email);
    $this->assertGuest();
});

test('user cannot reset password without providing a new password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->from(passwordResetGetRoute($token = getValidToken($user)))->post(passwordResetPostRoute(), [
        'token' => $token,
        'email' => $user->email,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect(passwordResetGetRoute($token));
    $response->assertSessionHasErrors('password');
    expect(session()->hasOldInput('email'))->toBeTrue();
    expect(session()->hasOldInput('password'))->toBeFalse();
    expect($user->fresh()->email)->toEqual($user->email);
    $this->assertGuest();
});

test('user cannot reset password without providing an email', function () {
    $user = User::factory()->create([
        'password' => bcrypt('old-password'),
    ]);

    $response = $this->from(passwordResetGetRoute($token = getValidToken($user)))->post(passwordResetPostRoute(), [
        'token' => $token,
        'email' => '',
        'password' => 'new-awesome-password',
        'password_confirmation' => 'new-awesome-password',
    ]);

    $response->assertRedirect(passwordResetGetRoute($token));
    $response->assertSessionHasErrors('email');
    expect(session()->hasOldInput('password'))->toBeFalse();
    expect($user->fresh()->email)->toEqual($user->email);
    $this->assertGuest();
});
