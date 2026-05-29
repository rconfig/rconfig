<?php

namespace Tests\Fasttests\ServiceTests;

use App\Jobs\CheckForUpdateJob;
use App\Services\Utilities\VersionCheckService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class VersionCheckServiceTest extends TestCase
{
    protected VersionCheckService $service;

    public function setUp(): void
    {
        parent::setUp();
        Cache::forget('version_check.status');
        config(['app.version' => '8.0.1']);
        $this->service = app()->make(VersionCheckService::class);
    }

    protected function tearDown(): void
    {
        Cache::forget('version_check.status');
        parent::tearDown();
    }

    public function test_refresh_persists_status_on_success(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response([['name' => 'core-8.5.0']], 200),
        ]);

        $status = $this->service->refresh();

        $this->assertSame('8.5.0', $status['latest_version']);
        $this->assertTrue($status['update_available']);
        $this->assertTrue($status['reachable']);
        $this->assertTrue($status['checked']);
        $this->assertSame(0, $status['consecutive_failures']);
        $this->assertNotNull(Cache::get('version_check.status'));
    }

    public function test_consecutive_failures_increment_across_refreshes(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response('', 500),
        ]);

        $this->assertSame(1, $this->service->refresh()['consecutive_failures']);
        $this->assertSame(2, $this->service->refresh()['consecutive_failures']);

        $third = $this->service->refresh();
        $this->assertSame(3, $third['consecutive_failures']);
        $this->assertFalse($third['reachable']);
        $this->assertTrue($third['checked']);
        $this->assertNotNull($third['last_error']);
    }

    public function test_keeps_last_known_version_when_github_becomes_unreachable(): void
    {
        // First call succeeds, second call fails (Http::fake merges stubs, so a
        // sequence is used to return different responses across the two calls).
        Http::fakeSequence('api.github.com/*')
            ->push([['name' => 'core-9.9.9']], 200)
            ->push('', 503);

        $this->assertTrue($this->service->refresh()['reachable']);

        $status = $this->service->refresh();

        $this->assertFalse($status['reachable']);
        $this->assertSame('9.9.9', $status['latest_version']);
        $this->assertTrue($status['update_available']);
    }

    public function test_logs_warning_then_error_as_failures_persist(): void
    {
        Log::spy();
        Http::fake([
            'api.github.com/*' => Http::response('', 500),
        ]);

        $this->service->refresh();
        $this->service->refresh();
        $this->service->refresh();

        Log::shouldHaveReceived('warning')->twice();
        Log::shouldHaveReceived('error')->once();
    }

    public function test_get_status_dispatches_job_when_no_status_cached(): void
    {
        Bus::fake();

        $status = $this->service->getStatus();

        $this->assertFalse($status['checked']);
        $this->assertSame('8.0.1', $status['current_version']);
        Bus::assertDispatched(CheckForUpdateJob::class);
    }

    public function test_job_refreshes_persisted_status(): void
    {
        Http::fake([
            'api.github.com/*' => Http::response([['name' => 'core-8.0.2']], 200),
        ]);

        CheckForUpdateJob::dispatchSync();

        $cached = Cache::get('version_check.status');
        $this->assertNotNull($cached);
        $this->assertSame('8.0.2', $cached['latest_version']);
        $this->assertTrue($cached['reachable']);
    }
}
