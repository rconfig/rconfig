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

    public function test_saml2_callback_provider_failure_shows_driver_specific_label()
    {
        Socialite::shouldReceive('driver->user')->andThrow(new \Exception('provider unreachable'));

        $response = $this->post('/auth/callback/saml2', ['SAMLResponse' => 'encoded-response']);

        $response->assertRedirect('/login');
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Unable to authenticate using SAML2', session('message'));
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

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }
}
