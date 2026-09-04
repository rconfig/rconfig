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
function dashboardHealthApiV2AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('dashboard health latest returns 200', function () {
    $this->withHeaders(dashboardHealthApiV2AuthHeader($this->token))
        ->getJson('/api/v2/dashboard/health-latest')
        ->assertStatus(200);
});
