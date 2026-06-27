<?php

namespace Tests\Fasttests\ControllersTests\Api\RestApi;

use App\Jobs\DownloadConfigNowJob;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\User;
use Queue;
use Tests\TestCase;

class MiscApiV1Test extends TestCase
{
    protected RestApiToken $token;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        User::factory()->create();
        $this->token = RestApiToken::factory()->create();
        Queue::fake();
    }

    /**
     * @return array<string, string>
     */
    private function authHeader(): array
    {
        return ['apitoken' => $this->token->api_token];
    }

    public function test_apitest_returns_success(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/apitest')
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function test_sysinfo_returns_200(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/sysinfo')
            ->assertStatus(200);
    }

    public function test_configinfo_returns_200(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/configinfo')
            ->assertStatus(200);
    }

    public function test_health_latest_returns_200(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/health-latest')
            ->assertStatus(200);
    }

    public function test_download_now_dispatches_job(): void
    {
        $device = Device::factory()->create();

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/download-now/' . $device->id)
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        Queue::assertPushed(DownloadConfigNowJob::class);
    }

    public function test_download_now_missing_device_returns_404(): void
    {
        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/download-now/999888777')
            ->assertStatus(404)
            ->assertJsonFragment(['success' => false]);
    }
}
