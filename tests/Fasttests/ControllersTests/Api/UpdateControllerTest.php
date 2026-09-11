<?php

use App\Jobs\CheckForUpdateJob;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    Cache::forget('version_check.status');
    config(['app.version' => '8.0.1']);
});

afterEach(function () {
    Cache::forget('version_check.status');
    $this->rollBackTransaction();
});

test('recheck performs live check and reports update available', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            ['name' => 'core-8.0.0'],
            ['name' => 'core-9.9.9'],
            ['name' => 'core-8.5.0'],
        ], 200),
    ]);

    $response = $this->json('get', '/api/version-check?clearCache=true');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => ['current_version', 'latest_version', 'update_available', 'latest_url', 'reachable', 'checked', 'last_checked_at', 'consecutive_failures', 'last_error'],
        'message',
    ]);
    $response->assertJsonPath('data.update_available', true);
    $response->assertJsonPath('data.latest_version', '9.9.9');
    $response->assertJsonPath('data.reachable', true);
    $response->assertJsonPath('data.checked', true);
    $response->assertJsonPath('data.consecutive_failures', 0);
    $response->assertJsonPath('data.latest_url', 'https://github.com/rconfig/rconfig/releases/tag/core-9.9.9');
});

test('recheck reports up to date when no newer tag exists', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            ['name' => 'core-7.0.0'],
            ['name' => 'core-8.0.1'],
        ], 200),
    ]);

    $response = $this->json('get', '/api/version-check?clearCache=true');

    $response->assertStatus(200);
    $response->assertJsonPath('data.update_available', false);
    $response->assertJsonPath('data.latest_version', '8.0.1');
    $response->assertJsonPath('data.reachable', true);
});

test('recheck ignores tags without core prefix', function () {
    Http::fake([
        'api.github.com/*' => Http::response([
            ['name' => 'pro-9.0.0'],
            ['name' => 'v9.0.0'],
            ['name' => 'core-8.0.2'],
        ], 200),
    ]);

    $response = $this->json('get', '/api/version-check?clearCache=true');

    $response->assertStatus(200);
    $response->assertJsonPath('data.latest_version', '8.0.2');
    $response->assertJsonPath('data.update_available', true);
});

test('recheck handles github being unreachable', function () {
    Http::fake([
        'api.github.com/*' => Http::response('', 500),
    ]);

    $response = $this->json('get', '/api/version-check?clearCache=true');

    $response->assertStatus(200);
    $response->assertJsonPath('data.reachable', false);
    $response->assertJsonPath('data.update_available', false);
    $response->assertJsonPath('data.checked', true);
    $response->assertJsonPath('data.consecutive_failures', 1);
    $response->assertJsonPath('data.current_version', '8.0.1');
    expect($response->json('data.last_error'))->not->toBeNull();
});

test('default get reads persisted status without calling github', function () {
    Http::preventStrayRequests();
    Cache::put('version_check.status', [
        'latest_version' => '8.2.0',
        'latest_url' => 'https://github.com/rconfig/rconfig/releases/tag/core-8.2.0',
        'reachable' => true,
        'checked' => true,
        'last_checked_at' => '2026-05-29T10:00:00+00:00',
        'last_success_at' => '2026-05-29T10:00:00+00:00',
        'consecutive_failures' => 0,
        'last_error' => null,
    ], 600);

    $response = $this->json('get', '/api/version-check');

    $response->assertStatus(200);
    $response->assertJsonPath('data.latest_version', '8.2.0');
    $response->assertJsonPath('data.update_available', true);
    $response->assertJsonPath('data.last_checked_at', '2026-05-29T10:00:00+00:00');
    Http::assertNothingSent();
});

test('default get with no status dispatches background job', function () {
    Bus::fake();

    $response = $this->json('get', '/api/version-check');

    $response->assertStatus(200);
    $response->assertJsonPath('data.checked', false);
    Bus::assertDispatched(CheckForUpdateJob::class);
});
