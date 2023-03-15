<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Tests\TestCase;

class MonitoredScheduledTaskLogItemControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function show_single_taskLogItem()
    {
        \App\Models\MonitoredScheduledTasks::factory()->create(['task_id' => 555555]);
        \App\Models\MonitoredScheduledTasks::factory()->create(['task_id' => 444444]);

        \App\Models\MonitoredScheduledTaskLogItems::factory(100)->create();
        \App\Models\MonitoredScheduledTaskLogItems::factory(50)->create(['task_id' => 555555]);

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

        $this->assertEquals(15, count($response['data']));
    }

    /** @test */
    public function get_all_taskLogItem()
    {
        \App\Models\MonitoredScheduledTasks::factory()->create(['task_id' => 444444]);

        \App\Models\MonitoredScheduledTaskLogItems::factory(100)->create(['task_id' => 444444]);

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
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }
}
