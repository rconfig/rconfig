<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->beginTransaction();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function successfulLoginRoute()
{
    return 'dashboard';
}

function loginGetRoute()
{
    return route('login');
}

function loginPostRoute()
{
    return route('login');
}

function logoutRoute()
{
    return route('logout');
}

function successfulLogoutRoute()
{
    return '/login';
}

function guestMiddlewareRoute()
{
    return route('login');
}

function getTooManyLoginAttemptsMessage()
{
    return sprintf('/^%s$/', str_replace('\:seconds', '\d+', preg_quote(__('auth.throttle'), '/')));
}

test('test user can view a login form', function () {
    $response = $this->get(loginGetRoute());

    $response->assertSuccessful();
    $response->assertViewIs('auth.login');
});

test('test user cannot view a login form when authenticated', function () {
    $user = User::factory()->make();

    $response = $this->actingAs($user)->get(loginGetRoute());

    $response->assertRedirect(successfulLoginRoute());
});

test('test user can login with correct credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt($password = 'i-love-laravel'),
    ]);

    $response = $this->post(loginPostRoute(), [
        'username' => $user->email,
        'password' => $password,
    ]);

    // dd($response->getContent());
    $response->assertRedirect(successfulLoginRoute());
    $this->assertAuthenticatedAs($user);
});

test('test user can login with correct username only credentials', function () {
    $user = User::factory()->create([
        // 'username' => 'joe.satriani',
        'password' => bcrypt($password = 'i-love-laravel'),
    ]);

    // dd($user);
    $response = $this->post(loginPostRoute(), [
        // 'email' => $user->email,
        'username' => $user->username,
        'password' => $password,
    ]);

    // dd($response->getContent());
    $response->assertRedirect(successfulLoginRoute());
    $this->assertAuthenticatedAs($user);
});

test('test remember me functionality', function () {
    $user = User::factory()->create([
        'id' => random_int(100, 10000),
        'password' => bcrypt($password = 'i-love-laravel'),
    ]);

    $response = $this->post(loginPostRoute(), [
        'username' => $user->email,
        'password' => $password,
        'remember' => 'on',
    ]);

    $user = $user->fresh();

    $response->assertRedirect(successfulLoginRoute());
    $response->assertCookie(Auth::guard()->getRecallerName());

    $recallerCookie = $response->getCookie(Auth::guard()->getRecallerName());
    expect($recallerCookie)->not->toBeNull();

    $cookieParts = explode('|', (string) $recallerCookie->getValue());
    expect($cookieParts)->toHaveCount(3);
    expect($cookieParts[0])->toBe((string) $user->id);
    expect($cookieParts[1])->toBe((string) $user->getRememberToken());
    $this->assertNotSame('', $cookieParts[2]);
    $this->assertAuthenticatedAs($user);
});

test('test user cannot login with incorrect password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('i-love-laravel'),
    ]);

    $response = $this->from(loginGetRoute())->post(loginPostRoute(), [
        'username' => $user->email,
        'password' => 'invalid-password',
    ]);

    $response->assertRedirect(loginGetRoute());
    $response->assertSessionHasErrors('username');
    expect(session()->hasOldInput('username'))->toBeTrue();
    expect(session()->hasOldInput('password'))->toBeFalse();
    $this->assertGuest();
});

test('test user cannot login with email that does not exist', function () {
    $response = $this->from(loginGetRoute())->post(loginPostRoute(), [
        'username' => 'nobody@example.com',
        'password' => 'invalid-password',
    ]);

    $response->assertRedirect(loginGetRoute());
    $response->assertSessionHasErrors('username');
    expect(session()->hasOldInput('username'))->toBeTrue();
    expect(session()->hasOldInput('password'))->toBeFalse();
    $this->assertGuest();
});

test('test user can logout', function () {
    $this->be(User::factory()->create());

    $response = $this->post(logoutRoute());

    $response->assertRedirect(successfulLogoutRoute());
    $this->assertGuest();
});

test('test user cannot logout when not authenticated', function () {
    $response = $this->get(logoutRoute());

    $response->assertRedirect(successfulLogoutRoute());
    $this->assertGuest();
});

test('test user cannot make more than five attempts in one minute', function () {
    $user = User::factory()->create([
        'password' => bcrypt($password = 'i-love-laravel'),
    ]);

    foreach (range(0, 10) as $_) {
        $response = $this->from(loginGetRoute())->post(loginPostRoute(), [
            'username' => $user->email,
            'password' => 'invalid-password',
        ]);
    }

    $response->assertRedirect(loginGetRoute());
    $response->assertSessionHasErrors('username');

    expect(collect(
        $response
            ->baseResponse
            ->getSession()
            ->get('errors')
            ->getBag('default')
            ->get('username')
    )->first())->toMatch(getTooManyLoginAttemptsMessage());
    expect(session()->hasOldInput('username'))->toBeTrue();
    expect(session()->hasOldInput('password'))->toBeFalse();
    $this->assertGuest();
});
