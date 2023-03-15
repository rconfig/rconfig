<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Tests\TestCase;

class TrackedJobControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function show_single_latest_tracked_job()
    {
        $job = \App\Models\TrackedJob::factory(100)->create();
        $response = $this->get('/api/tracked-jobs/'.$job[0]->device_id);

        $response->assertStatus(200);

        $response->assertJsonCount(13, 'data');
        $response->assertJsonCount(3);
        $response->assertJsonFragment([
            'device_id' => $job[0]->device_id,
        ]);
    }
}
