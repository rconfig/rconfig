<?php

use App\Models\MonitoredScheduledTaskLogItems;
use App\Models\MonitoredScheduledTasks;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('show single task log item', function () {
    MonitoredScheduledTasks::factory()->create(['task_id' => 555555]);
    MonitoredScheduledTasks::factory()->create(['task_id' => 444444]);

    MonitoredScheduledTaskLogItems::factory(100)->create();
    MonitoredScheduledTaskLogItems::factory(50)->create(['task_id' => 555555]);

    $response = $this->get('/api/tasks/monitored/444444');

    $response->assertJsonStructure([
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ]);

    expect(count($response['data']))->toEqual(15);
});

test('get all task log item', function () {
    MonitoredScheduledTasks::factory()->create(['task_id' => 444444]);

    MonitoredScheduledTaskLogItems::factory(100)->create(['task_id' => 444444]);

    $response = $this->get('/api/tasks/monitored/?page=1&perPage=100');
    $response->assertJsonStructure([
        'current_page',
        'data',
        'first_page_url',
        'from',
        'last_page',
        'last_page_url',
        'next_page_url',
        'path',
        'per_page',
        'prev_page_url',
        'to',
        'total',
    ]);
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});
