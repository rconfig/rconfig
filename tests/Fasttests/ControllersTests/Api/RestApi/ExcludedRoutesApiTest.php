<?php

use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function excludedRoutesAuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('v1 snippets route not registered', function () {
    $this->withHeaders(excludedRoutesAuthHeader($this->token))
        ->getJson('/api/v1/snippets')
        ->assertStatus(404);
});

test('v1 device profiles route not registered', function () {
    $this->withHeaders(excludedRoutesAuthHeader($this->token))
        ->getJson('/api/v1/device-profiles')
        ->assertStatus(404);
});

test('v2 policy compliance route not registered', function () {
    $this->withHeaders(excludedRoutesAuthHeader($this->token))
        ->getJson('/api/v2/policy-compliance-reports/export')
        ->assertStatus(404);
});
