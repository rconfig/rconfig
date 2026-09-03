<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Health\Models\HealthCheckResultHistoryItem;
use Tests\TestCase;

class SystemHealthControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->seedLatestHealthCheckBatch();
    }

    /**
     * The health-latest endpoint returns every row sharing the most recent batch, one
     * per check type. Build that batch directly rather than relying on a global seed.
     */
    private function seedLatestHealthCheckBatch(): void
    {
        $batch = (string) Str::uuid();

        foreach (['Database', 'Cache', 'CpuLoad', 'Horizon', 'Ping', 'Redis', 'Schedule', 'UsedDiskSpace'] as $checkName) {
            HealthCheckResultHistoryItem::create([
                'check_name' => $checkName,
                'check_label' => $checkName,
                'status' => 'ok',
                'notification_message' => '',
                'short_summary' => 'Ok',
                'meta' => [],
                'ended_at' => now(),
                'batch' => $batch,
            ]);
        }
    }

    public function test_sys_environment_latest()
    {
        $response = $this->json('get', '/api/dashboard/health-latest');

        $response->assertStatus(200);
        $this->assertCount(8, $response->json()['data']);
    }
}
