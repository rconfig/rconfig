<?php

namespace Tests\Fasttests\Auth;

use Tests\TestCase;

class AuthProvidersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    public function test_saml2_display_name_is_null_when_saml2_is_not_configured()
    {
        config(['services.saml2.metadata' => null]);

        $response = $this->getJson('/api/auth/providers');

        $response->assertSuccessful();
        $response->assertJson([
            'saml2' => false,
            'saml2_display_name' => null,
        ]);
    }

    public function test_saml2_display_name_is_returned_when_saml2_is_configured()
    {
        config([
            'services.saml2.metadata' => 'https://idp.example.com/metadata',
            'services.saml2.display_name' => 'Acme Corp SSO',
        ]);

        $response = $this->getJson('/api/auth/providers');

        $response->assertSuccessful();
        $response->assertJson([
            'saml2' => true,
            'saml2_display_name' => 'Acme Corp SSO',
        ]);
    }

    public function test_saml2_display_name_falls_back_to_default_when_configured_without_custom_name()
    {
        config([
            'services.saml2.metadata' => 'https://idp.example.com/metadata',
            'services.saml2.display_name' => 'Shibboleth', // env default from config/services.php
        ]);

        $response = $this->getJson('/api/auth/providers');

        $response->assertSuccessful();
        $response->assertJson([
            'saml2' => true,
            'saml2_display_name' => 'Shibboleth',
        ]);
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        parent::tearDown();
    }
}
