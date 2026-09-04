<?php

use App\Models\ConfigChange;
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
function configChangesApiV2AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('show existing change', function () {
    $change = ConfigChange::factory()->create();

    $this->withHeaders(configChangesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/config-changes/' . $change->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('show missing change returns 404', function () {
    $this->withHeaders(configChangesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/config-changes/99999999')
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});

test('by config existing returns change', function () {
    $change = ConfigChange::factory()->create();

    $this->withHeaders(configChangesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/config-changes/by-config/' . $change->current_config_id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('by config missing returns 404', function () {
    $this->withHeaders(configChangesApiV2AuthHeader($this->token))
        ->getJson('/api/v2/config-changes/by-config/99999999')
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});
