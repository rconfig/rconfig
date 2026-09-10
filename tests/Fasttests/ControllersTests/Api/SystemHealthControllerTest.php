<?php

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Health\Models\HealthCheckResultHistoryItem;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    seedLatestHealthCheckBatch();
});

/**
 * The health-latest endpoint returns every row sharing the most recent batch, one
 * per check type. Build that batch directly rather than relying on a global seed.
 */
function seedLatestHealthCheckBatch(): void
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

test('sys environment latest', function () {
    $response = $this->json('get', '/api/dashboard/health-latest');

    $response->assertStatus(200);
    expect($response->json()['data'])->toHaveCount(8);
});
