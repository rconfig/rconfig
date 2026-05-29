<?php

// https://github.com/DCzajkowski/auth-tests

namespace Tests\Fasttests\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    protected function successfulLoginRoute()
    {
        return 'dashboard';
    }

    protected function loginGetRoute()
    {
        return route('login');
    }

    protected function loginPostRoute()
    {
        return route('login');
    }

    protected function logoutRoute()
    {
        return route('logout');
    }

    protected function successfulLogoutRoute()
    {
        return '/login';
    }

    protected function guestMiddlewareRoute()
    {
        return route('login');
    }

    protected function getTooManyLoginAttemptsMessage()
    {
        return sprintf('/^%s$/', str_replace('\:seconds', '\d+', preg_quote(__('auth.throttle'), '/')));
    }

    public function test_test_user_can_view_a_login_form()
    {
        $response = $this->get($this->loginGetRoute());

        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    public function test_test_user_cannot_view_a_login_form_when_authenticated()
    {
        $user = User::factory()->make();

        $response = $this->actingAs($user)->get($this->loginGetRoute());

        $response->assertRedirect($this->successfulLoginRoute());
    }

    public function test_test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $response = $this->post($this->loginPostRoute(), [
            'username' => $user->email,
            'password' => $password,
        ]);
        // dd($response->getContent());
        $response->assertRedirect($this->successfulLoginRoute());
        $this->assertAuthenticatedAs($user);
    }

    public function test_test_user_can_login_with_correct_username_only_credentials()
    {
        $user = User::factory()->create([
            // 'username' => 'joe.satriani',
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);
        // dd($user);
        $response = $this->post($this->loginPostRoute(), [
            // 'email' => $user->email,
            'username' => $user->username,
            'password' => $password,
        ]);
        // dd($response->getContent());
        $response->assertRedirect($this->successfulLoginRoute());
        $this->assertAuthenticatedAs($user);
    }

    public function test_test_remember_me_functionality()
    {
        $user = User::factory()->create([
            'id' => random_int(100, 10000),
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $response = $this->post($this->loginPostRoute(), [
            'username' => $user->email,
            'password' => $password,
            'remember' => 'on',
        ]);

        $user = $user->fresh();

        $response->assertRedirect($this->successfulLoginRoute());
        $response->assertCookie(Auth::guard()->getRecallerName());

        $recallerCookie = $response->getCookie(Auth::guard()->getRecallerName());
        $this->assertNotNull($recallerCookie);

        $cookieParts = explode('|', (string) $recallerCookie->getValue());
        $this->assertCount(3, $cookieParts);
        $this->assertSame((string) $user->id, $cookieParts[0]);
        $this->assertSame((string) $user->getRememberToken(), $cookieParts[1]);
        $this->assertNotSame('', $cookieParts[2]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_test_user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('i-love-laravel'),
        ]);

        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'username' => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect($this->loginGetRoute());
        $response->assertSessionHasErrors('username');
        $this->assertTrue(session()->hasOldInput('username'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_test_user_cannot_login_with_email_that_does_not_exist()
    {
        $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
            'username' => 'nobody@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect($this->loginGetRoute());
        $response->assertSessionHasErrors('username');
        $this->assertTrue(session()->hasOldInput('username'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_test_user_can_logout()
    {
        $this->be(User::factory()->create());

        $response = $this->post($this->logoutRoute());

        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }

    public function test_test_user_cannot_logout_when_not_authenticated()
    {
        $response = $this->get($this->logoutRoute());

        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }

    public function test_test_user_cannot_make_more_than_five_attempts_in_one_minute()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        foreach (range(0, 10) as $_) {
            $response = $this->from($this->loginGetRoute())->post($this->loginPostRoute(), [
                'username' => $user->email,
                'password' => 'invalid-password',
            ]);
        }

        $response->assertRedirect($this->loginGetRoute());
        $response->assertSessionHasErrors('username');

        $this->assertMatchesRegularExpression(
            $this->getTooManyLoginAttemptsMessage(),
            collect(
                $response
                    ->baseResponse
                    ->getSession()
                    ->get('errors')
                    ->getBag('default')
                    ->get('username')
            )->first()
        );
        $this->assertTrue(session()->hasOldInput('username'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
