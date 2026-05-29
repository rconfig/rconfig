<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Jobs\CheckForUpdateJob;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateControllerTest extends TestCase
{
    /** @var User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Cache::forget('version_check.status');
        config(['app.version' => '8.0.1']);
    }

    protected function tearDown(): void
    {
        Cache::forget('version_check.status');
        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_recheck_performs_live_check_and_reports_update_available(): void
    {
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
    }

    public function test_recheck_reports_up_to_date_when_no_newer_tag_exists(): void
    {
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
    }

    public function test_recheck_ignores_tags_without_core_prefix(): void
    {
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
    }

    public function test_recheck_handles_github_being_unreachable(): void
    {
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
        $this->assertNotNull($response->json('data.last_error'));
    }

    public function test_default_get_reads_persisted_status_without_calling_github(): void
    {
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
    }

    public function test_default_get_with_no_status_dispatches_background_job(): void
    {
        Bus::fake();

        $response = $this->json('get', '/api/version-check');

        $response->assertStatus(200);
        $response->assertJsonPath('data.checked', false);
        Bus::assertDispatched(CheckForUpdateJob::class);
    }
}
