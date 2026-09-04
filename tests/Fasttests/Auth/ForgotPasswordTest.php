<?php

// https://github.com/DCzajkowski/auth-tests
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function passwordRequestRoute()
{
    return route('password.request');
}

function passwordEmailGetRoute()
{
    return route('password.email');
}

function passwordEmailPostRoute()
{
    return route('password.email');
}

test('user can view an email password form', function () {
    $response = $this->get(passwordRequestRoute());

    $response->assertSuccessful();
    $response->assertViewIs('auth.passwords.email');
});

test('user receives an email with a password reset link', function () {
    $this->withoutMiddleware();
    Notification::fake();
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);
    $response = $this->post(passwordEmailPostRoute(), [
        'email' => 'john@example.com',
    ]);

    expect($token = DB::table('password_resets')->first())->not->toBeNull();
    Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
        return Hash::check($notification->token, $token->token) === true;
    });
});

test('user does not receive email when not registered', function () {
    $this->withoutMiddleware();
    Notification::fake();

    $response = $this->from(passwordEmailGetRoute())->post(passwordEmailPostRoute(), [
        'email' => 'nobody@example.com',
    ]);

    $response->assertRedirect(passwordEmailGetRoute());
    $response->assertSessionHasErrors('email');
    Notification::assertNotSentTo(User::factory()->make(['email' => 'nobody@example.com']), ResetPassword::class);
});

test('email is required', function () {
    $this->withoutMiddleware();
    $response = $this->from(passwordEmailGetRoute())->post(passwordEmailPostRoute(), []);

    $response->assertRedirect(passwordEmailGetRoute());
    $response->assertSessionHasErrors('email');
});

test('email is a valid email', function () {
    $this->withoutMiddleware();
    $response = $this->from(passwordEmailGetRoute())->post(passwordEmailPostRoute(), [
        'email' => 'invalid-email',
    ]);

    $response->assertRedirect(passwordEmailGetRoute());
    $response->assertSessionHasErrors('email');
});
