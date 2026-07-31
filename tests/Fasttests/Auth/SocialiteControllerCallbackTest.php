<?php

namespace Tests\Fasttests\Auth;

use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class SocialiteControllerCallbackTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    public function test_saml2_callback_missing_saml_response_shows_specific_message()
    {
        // Regression test: SocialAuthHandler::checkErrors() returns a
        // RedirectResponse with a specific message, but the controller used to
        // discard it whenever the result wasn't a User and substitute a
        // generic "SAML2 authentication failed" message instead. This
        // confirms the specific message now reaches the user through the
        // real route, not just through a direct call to checkErrors().
        $response = $this->post('/auth/callback/saml2');

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('SAML response is missing. Please try again.', session('message'));
        $this->assertStringNotContainsString('SAML2 authentication failed', session('message'));
    }

    public function test_google_callback_missing_code_shows_specific_message()
    {
        $response = $this->get('/auth/callback/google');

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Authorization code is missing. Please try again.', session('message'));
        $this->assertStringNotContainsString('Google authentication failed', session('message'));
    }

    public function test_google_callback_provider_failure_shows_driver_specific_label()
    {
        Socialite::shouldReceive('driver->user')->andThrow(new \Exception('provider unreachable'));

        $response = $this->get('/auth/callback/google?code=some-code');

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Unable to authenticate using Google', session('message'));
        $this->assertStringNotContainsString('Google authentication failed', session('message'));
    }

    public function test_saml2_callback_provider_failure_shows_configured_display_name()
    {
        // driverLabel() now reads services.saml2.display_name (the same
        // config that drives the login button text) instead of a hardcoded
        // 'SAML2' string, so a deployment with a custom display name shows
        // a matching error message.
        config(['services.saml2.display_name' => 'Company SSO']);

        Socialite::shouldReceive('driver->user')->andThrow(new \Exception('provider unreachable'));

        $response = $this->post('/auth/callback/saml2', ['SAMLResponse' => 'encoded-response']);

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Unable to authenticate using Company SSO', session('message'));
        $this->assertStringNotContainsString('SAML2 authentication failed', session('message'));
    }

    public function test_google_callback_unregistered_account_shows_specific_message()
    {
        Socialite::shouldReceive('driver->user')->andReturn(null);

        $response = $this->get('/auth/callback/google?code=some-code');

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Your account is not registered', session('message'));
        $this->assertStringNotContainsString('Google authentication failed', session('message'));
    }

    public function test_google_callback_shows_generic_error_when_register_returns_null()
    {
        // Regression test: register() can return null (not a User, not a
        // Response) via the 23000/duplicate-key branch in *Auth::register(),
        // when User::where('email', ...)->first() finds no matching row --
        // e.g. the integrity violation was on a different constraint than
        // the email unique key. Without an explicit fallback for this case,
        // the controller would return null directly, which Laravel renders
        // as a blank 200 response instead of any error message.
        //
        // SocialiteController instantiates GoogleAuth directly (new GoogleAuth)
        // rather than resolving it from the container, so we use Mockery's
        // class-overload mocking to control what register() returns without
        // needing to organically reproduce a specific DB integrity violation.
        $mock = \Mockery::mock('overload:' . \App\Services\SocialAuth\GoogleAuth::class);
        $mock->shouldReceive('register')->andReturn(null);

        $response = $this->get('/auth/callback/google?code=some-code');

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Authentication failed. Please contact your administrator.', session('message'));
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }
}
