<?php

use App\Models\TrackedJob;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('show single latest tracked job', function () {
    $job = TrackedJob::factory(100)->create();
    $response = $this->get('/api/tracked-jobs/' . $job[0]->device_id);

    $response->assertStatus(200);

    $response->assertJsonCount(13, 'data');
    $response->assertJsonCount(3);
    $response->assertJsonFragment([
        'device_id' => $job[0]->device_id,
    ]);
});
