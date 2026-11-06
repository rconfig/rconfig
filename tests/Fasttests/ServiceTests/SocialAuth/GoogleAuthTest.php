<?php

use App\Models\User;
use App\Services\SocialAuth\GoogleAuth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    $this->beginTransaction();
});

test('login redirects with error if code is missing', function () {
    $request = Request::create('/login', 'GET');

    $service = new GoogleAuth;

    $response = $service->register($request);
    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Authorization code is missing. Please try again.', session('message'));
});

test('login redirects with error if access is denied', function () {
    $request = Request::create('/login', 'GET', ['denied' => true, 'code' => '1234']);

    $service = new GoogleAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Access was denied. Please try again.', session('message'));
});

test('login redirects with error if provider fails', function () {
    $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

    Socialite::shouldReceive('driver->user')->andThrow(new Exception);

    $service = new GoogleAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Unable to authenticate using Google', session('message'));
});

test('login redirects with error if user not found', function () {
    $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

    Socialite::shouldReceive('driver->user')->andReturn(null);

    $service = new GoogleAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    expect(session()->all())->toHaveKey('message');
    $this->assertStringContainsString('Your account is not registered', session('message'));
});

test('login logs the error if saving the user fails', function () {
    // Only MicrosoftAuth logged a failed user save; the others flashed the
    // error to the login screen and left no server-side record.
    User::creating(fn () => throw new RuntimeException('simulated write failure'));

    $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

    Socialite::shouldReceive('driver->user')->andReturn((new SocialiteUser)->map([
        'id' => 'google-save-failure',
        'name' => 'Google Save Failure',
        'email' => 'google-save-failure@example.com',
    ]));

    $service = new GoogleAuth;

    $response = $service->register($request);

    expect($response->getStatusCode())->toEqual(302);
    $this->assertStringContainsString('An error occurred while updating your information', session('message'));
    $this->assertDatabaseHas('activity_log', [
        'description' => 'An error occurred while updating your information. Please contact support. Error: simulated write failure',
    ]);
});

afterEach(function () {
    $this->rollbackTransaction();
});
