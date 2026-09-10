<?php

use App\Services\SocialAuth\GoogleAuth;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->beginTransaction();
});

test('saml2 callback missing saml response shows specific message', function () {
    // Regression test: SocialAuthHandler::checkErrors() returns a
    // RedirectResponse with a specific message, but the controller used to
    // discard it whenever the result wasn't a User and substitute a
    // generic "SAML2 authentication failed" message instead. This
    // confirms the specific message now reaches the user through the
    // real route, not just through a direct call to checkErrors().
    $response = $this->post('/auth/callback/saml2');

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('SAML response is missing. Please try again.', session('message'));
    $this->assertStringNotContainsString('SAML2 authentication failed', session('message'));
});

test('google callback missing code shows specific message', function () {
    $response = $this->get('/auth/callback/google');

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Authorization code is missing. Please try again.', session('message'));
    $this->assertStringNotContainsString('Google authentication failed', session('message'));
});

test('google callback provider failure shows driver specific label', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new Exception('provider unreachable'));

    $response = $this->get('/auth/callback/google?code=some-code');

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Unable to authenticate using Google', session('message'));
    $this->assertStringNotContainsString('Google authentication failed', session('message'));
});

test('saml2 callback provider failure shows configured display name', function () {
    // driverLabel() now reads services.saml2.display_name (the same
    // config that drives the login button text) instead of a hardcoded
    // 'SAML2' string, so a deployment with a custom display name shows
    // a matching error message.
    config(['services.saml2.display_name' => 'Company SSO']);

    Socialite::shouldReceive('driver->user')->andThrow(new Exception('provider unreachable'));

    $response = $this->post('/auth/callback/saml2', ['SAMLResponse' => 'encoded-response']);

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Unable to authenticate using Company SSO', session('message'));
    $this->assertStringNotContainsString('SAML2 authentication failed', session('message'));
});

test('google callback unregistered account shows specific message', function () {
    Socialite::shouldReceive('driver->user')->andReturn(null);

    $response = $this->get('/auth/callback/google?code=some-code');

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Your account is not registered', session('message'));
    $this->assertStringNotContainsString('Google authentication failed', session('message'));
});

test('google callback shows generic error when register returns null', function () {
    // Regression test: register() can return null (not a User, not a
    // Response) via the 23000/duplicate-key branch in *Auth::register(),
    // when User::where('email', ...)->first() finds no matching row --
    // e.g. the integrity violation was on a different constraint than
    // the email unique key. Without an explicit fallback for this case,
    // the controller would return null directly, which Laravel renders
    // as a blank 200 response instead of any error message.
    //
    // SocialiteController now resolves GoogleAuth via app(GoogleAuth::class)
    // rather than `new GoogleAuth`, so it can be swapped out through
    // Laravel's standard container-mocking helper. This avoids Mockery's
    // 'overload:' mocking, which requires the class not be loaded yet --
    // fragile here since earlier tests in this class already trigger
    // GoogleAuth via the real callback route, and forcing process
    // isolation would re-trigger MigrateFreshSeedOnce's migrate:fresh
    // against the shared test database mid-suite.
    $this->mock(GoogleAuth::class, function ($mock) {
        $mock->shouldReceive('register')->andReturn(null);
    });

    $response = $this->get('/auth/callback/google?code=some-code');

    $response->assertRedirect('/login');
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Authentication failed. Please contact your administrator.', session('message'));
});

afterEach(function () {
    $this->rollbackTransaction();
});
