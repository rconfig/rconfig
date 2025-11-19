<?php

namespace Tests\Fasttests\ServiceTests\SocialAuth;

use App\Services\SocialAuth\OktaAuth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class OktaAuthTest extends TestCase
{
    /** @var \App\Models\User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    public function test_login_redirects_with_error_if_code_is_missing()
    {
        $request = Request::create('/login', 'GET');

        $service = new OktaAuth;

        $response = $service->register($request);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Authorization code is missing. Please try again.', session('message'));
    }

    public function test_login_redirects_with_error_if_access_is_denied()
    {
        $request = Request::create('/login', 'GET', ['denied' => true, 'code' => '1234']);

        $service = new OktaAuth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Access was denied. Please try again.', session('message'));
    }

    public function test_login_redirects_with_error_if_provider_fails()
    {
        $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

        Socialite::shouldReceive('driver->user')->andThrow(new \Exception);

        $service = new OktaAuth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Unable to authenticate using Microsoft', session('message'));
    }

    public function test_login_redirects_with_error_if_user_not_found()
    {
        $request = Request::create('/login', 'GET', ['code' => 'valid-code']);

        Socialite::shouldReceive('driver->user')->andReturn(null);

        $service = new OktaAuth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Your account is not registered', session('message'));
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }
}
