<?php

use App\Jobs\DownloadConfigNowJob;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    User::factory()->create();
    $this->token = RestApiToken::factory()->create();
    Queue::fake();
});

/**
 * @return array<string, string>
 */
function miscApiV1AuthHeader(RestApiToken $token): array
{
    return ['apitoken' => $token->api_token];
}

test('apitest returns success', function () {
    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/apitest')
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);
});

test('sysinfo returns 200', function () {
    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/sysinfo')
        ->assertStatus(200);
});

test('configinfo returns 200', function () {
    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/configinfo')
        ->assertStatus(200);
});

test('health latest returns 200', function () {
    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/health-latest')
        ->assertStatus(200);
});

test('download now dispatches job', function () {
    $device = Device::factory()->create();

    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/download-now/' . $device->id)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true]);

    Queue::assertPushed(DownloadConfigNowJob::class);
});

test('download now missing device returns 404', function () {
    $this->withHeaders(miscApiV1AuthHeader($this->token))
        ->getJson('/api/v1/download-now/999888777')
        ->assertStatus(404)
        ->assertJsonFragment(['success' => false]);
});
