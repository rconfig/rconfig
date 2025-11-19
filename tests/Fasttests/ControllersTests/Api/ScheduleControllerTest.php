<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Http\Controllers\Api\ScheduleController;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{
    public function test_list_returns_json_with_scheduled_tasks()
    {
        $controller = new ScheduleController();
        $response = $controller->list();
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('scheduled_tasks', $data);
        $this->assertIsArray($data['scheduled_tasks']);
    }

    public function test_list_includes_timezone_information()
    {
        $controller = new ScheduleController();
        
        $response = $controller->list();
        $data = json_decode($response->getContent(), true);
        
        if (!empty($data['scheduled_tasks'])) {
            $task = $data['scheduled_tasks'][0];
            $this->assertNotEmpty($task['timezone']);
        } else {
            $this->assertTrue(true); // No tasks to test
        }
    }
}