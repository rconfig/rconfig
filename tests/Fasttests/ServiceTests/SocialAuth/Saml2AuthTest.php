<?php

namespace Tests\Fasttests\ServiceTests\SocialAuth;

use App\Models\User;
use App\Services\SocialAuth\Saml2Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class Saml2AuthTest extends TestCase
{
    /** @var User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    public function test_login_redirects_with_error_if_saml_response_is_missing()
    {
        $request = Request::create('/login', 'GET');

        $service = new Saml2Auth;

        $response = $service->register($request);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('SAML response is missing. Please try again.', session('message'));
    }

    public function test_login_does_not_require_oauth_code_param()
    {
        // A SAML2 callback never carries an OAuth-style 'code' param, only
        // SAMLResponse/SAMLart. Confirm the OAuth 'code' check is bypassed
        // for this driver and the flow proceeds past it once SAMLResponse
        // is present.
        $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

        Socialite::shouldReceive('driver->user')->andReturn(null);

        $service = new Saml2Auth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringNotContainsString('Authorization code is missing', session('message'));
        $this->assertStringContainsString('Your account is not registered', session('message'));
    }

    public function test_login_accepts_samlart_in_place_of_saml_response()
    {
        $request = Request::create('/login', 'GET', ['SAMLart' => 'artifact-value']);

        Socialite::shouldReceive('driver->user')->andReturn(null);

        $service = new Saml2Auth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringNotContainsString('SAML response is missing', session('message'));
    }

    public function test_login_ignores_stray_denied_param()
    {
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

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringNotContainsString('Access was denied', session('message'));
        $this->assertStringContainsString('Your account is not registered', session('message'));
    }

    public function test_login_redirects_with_error_if_provider_fails()
    {
        $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

        Socialite::shouldReceive('driver->user')->andThrow(new \Exception);

        $service = new Saml2Auth;

        $response = $service->register($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertArrayHasKey('message', session()->all());
        $this->assertStringContainsString('Unable to authenticate using Microsoft', session('message'));
    }

    public function test_login_redirects_with_error_if_user_not_found()
    {
        $request = Request::create('/login', 'POST', ['SAMLResponse' => 'encoded-response']);

        Socialite::shouldReceive('driver->user')->andReturn(null);

        $service = new Saml2Auth;

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
