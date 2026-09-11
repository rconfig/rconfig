<?php

use App\Models\RestApiToken;
use App\Models\User;
use App\Models\Vendor;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
});

/**
 * @return array<string, string>
 */
function vendorsApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('index returns vendors', function () {
    Vendor::factory(3)->create();

    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/vendors?perPage=50')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'total']);
});

test('show returns vendor', function () {
    $vendor = Vendor::factory()->create();

    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->getJson('/api/v1/vendors/' . $vendor->id)
        ->assertStatus(200)
        ->assertJsonFragment(['vendorName' => $vendor->vendorName]);
});

test('store creates vendor', function () {
    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/vendors', [
            'vendorName' => 'CiscoApiTest12345',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    $this->assertDatabaseHas('vendors', ['vendorName' => 'CiscoApiTest12345']);
});

test('store validation failure returns 422', function () {
    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->postJson('/api/v1/vendors', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['vendorName']);
});

test('update edits vendor', function () {
    $vendor = Vendor::factory()->create();

    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->patchJson('/api/v1/vendors/' . $vendor->id, [
            'vendorName' => 'a-new-vendor-name-api',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'vendorName' => 'a-new-vendor-name-api']);
});

test('destroy deletes vendor', function () {
    $vendor = Vendor::factory()->create();

    $this->withHeaders(vendorsApiV1AuthHeader($this->token))
        ->deleteJson('/api/v1/vendors/' . $vendor->id)
        ->assertStatus(200);

    $this->assertDatabaseMissing('vendors', ['id' => $vendor->id]);
});
