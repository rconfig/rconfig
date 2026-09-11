<?php

beforeEach(function () {
    $this->beginTransaction();
});

test('saml2 display name is null when saml2 is not configured', function () {
    config(['services.saml2.metadata' => null]);

    $response = $this->getJson('/api/auth/providers');

    $response->assertSuccessful();
    $response->assertJson([
        'saml2' => false,
        'saml2_display_name' => null,
    ]);
});

test('saml2 display name is returned when saml2 is configured', function () {
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
});

test('saml2 display name falls back to default when configured without custom name', function () {
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
});

afterEach(function () {
    $this->rollbackTransaction();
});
