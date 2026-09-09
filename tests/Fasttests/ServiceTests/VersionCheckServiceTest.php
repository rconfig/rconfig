<?php

use App\Jobs\CheckForUpdateJob;
use App\Services\Utilities\VersionCheckService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Cache::forget('version_check.status');
    config(['app.version' => '8.0.1']);
    $this->service = app()->make(VersionCheckService::class);
});

afterEach(function () {
    Cache::forget('version_check.status');
});

test('refresh persists status on success', function () {
    Http::fake([
        'api.github.com/*' => Http::response([['name' => 'core-8.5.0']], 200),
    ]);

    $status = $this->service->refresh();

    expect($status['latest_version'])->toBe('8.5.0');
    expect($status['update_available'])->toBeTrue();
    expect($status['reachable'])->toBeTrue();
    expect($status['checked'])->toBeTrue();
    expect($status['consecutive_failures'])->toBe(0);
    expect(Cache::get('version_check.status'))->not->toBeNull();
});

test('consecutive failures increment across refreshes', function () {
    Http::fake([
        'api.github.com/*' => Http::response('', 500),
    ]);

    expect($this->service->refresh()['consecutive_failures'])->toBe(1);
    expect($this->service->refresh()['consecutive_failures'])->toBe(2);

    $third = $this->service->refresh();
    expect($third['consecutive_failures'])->toBe(3);
    expect($third['reachable'])->toBeFalse();
    expect($third['checked'])->toBeTrue();
    expect($third['last_error'])->not->toBeNull();
});

test('keeps last known version when github becomes unreachable', function () {
    // First call succeeds, second call fails (Http::fake merges stubs, so a
    // sequence is used to return different responses across the two calls).
    Http::fakeSequence('api.github.com/*')
        ->push([['name' => 'core-9.9.9']], 200)
        ->push('', 503);

    expect($this->service->refresh()['reachable'])->toBeTrue();

    $status = $this->service->refresh();

    expect($status['reachable'])->toBeFalse();
    expect($status['latest_version'])->toBe('9.9.9');
    expect($status['update_available'])->toBeTrue();
});

test('logs warning then error as failures persist', function () {
    Log::spy();
    Http::fake([
        'api.github.com/*' => Http::response('', 500),
    ]);

    $this->service->refresh();
    $this->service->refresh();
    $this->service->refresh();

    Log::shouldHaveReceived('warning')->twice();
    Log::shouldHaveReceived('error')->once();
});

test('get status dispatches job when no status cached', function () {
    Bus::fake();

    $status = $this->service->getStatus();

    expect($status['checked'])->toBeFalse();
    expect($status['current_version'])->toBe('8.0.1');
    Bus::assertDispatched(CheckForUpdateJob::class);
});

test('job refreshes persisted status', function () {
    Http::fake([
        'api.github.com/*' => Http::response([['name' => 'core-8.0.2']], 200),
    ]);

    CheckForUpdateJob::dispatchSync();

    $cached = Cache::get('version_check.status');
    expect($cached)->not->toBeNull();
    expect($cached['latest_version'])->toBe('8.0.2');
    expect($cached['reachable'])->toBeTrue();
});
