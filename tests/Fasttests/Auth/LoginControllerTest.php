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

    /** @test */
    public function testUserCanViewALoginForm()
    {
        $response = $this->get($this->loginGetRoute());

        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function testUserCannotViewALoginFormWhenAuthenticated()
    {
        $user = User::factory()->make();

        $response = $this->actingAs($user)->get($this->loginGetRoute());

        $response->assertRedirect($this->successfulLoginRoute());
    }

    /** @test */
    public function testUserCanLoginWithCorrectCredentials()
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

    /** @test */
    public function testUserCanLoginWithCorrectUsernameOnlyCredentials()
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

    /** @test */
    public function testRememberMeFunctionality()
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
        $response->assertCookie(Auth::guard()->getRecallerName(), vsprintf('%s|%s|%s', [
            $user->id,
            $user->getRememberToken(),
            $user->password,
        ]));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function testUserCannotLoginWithIncorrectPassword()
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

    /** @test */
    public function testUserCannotLoginWithEmailThatDoesNotExist()
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

    /** @test */
    public function testUserCanLogout()
    {
        $this->be(User::factory()->create());

        $response = $this->post($this->logoutRoute());

        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }

    /** @test */
    public function testUserCannotLogoutWhenNotAuthenticated()
    {
        $response = $this->get($this->logoutRoute());

        $response->assertRedirect($this->successfulLogoutRoute());
        $this->assertGuest();
    }

    /** @test */
    public function testUserCannotMakeMoreThanFiveAttemptsInOneMinute()
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
