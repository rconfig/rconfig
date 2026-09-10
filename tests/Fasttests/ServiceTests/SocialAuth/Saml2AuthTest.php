<?php

use App\Services\SocialAuth\Saml2Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->beginTransaction();
});

test('login redirects with error if saml response is missing', function () {
    $request = Request::create('/login', 'GET');

    $service = new Saml2Auth;

    $response = $service->register($request);
    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('SAML response is missing. Please try again.', session('message'));
});

test('login does not require oauth code param', function () {
    // A SAML2 callback never carries an OAuth-style 'code' param, only
    // SAMLResponse/SAMLart. Confirm the OAuth 'code' check is bypassed
    // for this driver and the flow proceeds past it once SAMLResponse
    // is present.
    $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new Saml2Auth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringNotContainsString('Authorization code is missing', session('message'));
    $this->assertStringContainsString('Your account is not registered', session('message'));
});

test('login accepts samlart in place of saml response', function () {
    $request = Request::create('/login', 'GET', ['SAMLart' => 'artifact-value']);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new Saml2Auth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringNotContainsString('SAML response is missing', session('message'));
});

test('login ignores stray denied param', function () {
    // SAML2 has no OAuth-style 'denied' query param -- denial/errors are
    // encoded inside the SAMLResponse body itself. Confirm a stray
    // 'denied' param on a SAML2 callback does not trigger the
    // OAuth-specific "Access was denied" message and the flow proceeds
    // normally based on the SAMLResponse content.
    $request = Request::create('/login', 'POST', [
        'SAMLResponse' => 'encoded-response',
        'denied' => true,
    ]);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new Saml2Auth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringNotContainsString('Access was denied', session('message'));
    $this->assertStringContainsString('Your account is not registered', session('message'));
});

test('login redirects with error if provider fails', function () {
    // driverLabel() reads services.saml2.display_name (the same config
    // that drives the login button text) rather than a hardcoded
    // 'SAML2' string; set it explicitly so the assertion doesn't depend
    // on the .env default.
    config(['services.saml2.display_name' => 'Company SSO']);

    $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

    Socialite::shouldReceive('driver->user')->andThrow(new Exception);

    $service = new Saml2Auth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Unable to authenticate using Company SSO', session('message'));
});

test('login redirects with error if user not found', function () {
    $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new Saml2Auth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Your account is not registered', session('message'));
});

afterEach(function () {
    $this->rollbackTransaction();
});
