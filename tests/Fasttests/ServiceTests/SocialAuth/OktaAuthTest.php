<?php

use App\Services\SocialAuth\OktaAuth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

beforeEach(function () {
    $this->beginTransaction();
});

test('login redirects with error if code is missing', function () {
    $request = Request::create('/login', 'GET');

    $service = new OktaAuth;

    $response = $service->register($request);
    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Authorization code is missing. Please try again.', session('message'));
});

test('login redirects with error if access is denied', function () {
    $request = Request::create('/login', 'GET', ['denied' => true, 'code' => '1234']);

    $service = new OktaAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Access was denied. Please try again.', session('message'));
});

test('login redirects with error if provider fails', function () {
    $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

    Socialite::shouldReceive('driver->user')->andThrow(new Exception);

    $service = new OktaAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Unable to authenticate using Okta', session('message'));
});

test('login redirects with error if user not found', function () {
    $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new OktaAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Your account is not registered', session('message'));
});

afterEach(function () {
    $this->rollbackTransaction();
});
