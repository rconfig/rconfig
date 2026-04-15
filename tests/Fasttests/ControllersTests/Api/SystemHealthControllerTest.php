<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\User;

use Tests\TestCase;

class SystemHealthControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }


    public function test_sys_environment_latest()
    {
        $response = $this->json('get', '/api/dashboard/health-latest');

        $response->assertStatus(200);
        $this->assertCount(8, $response->json()['data']);
    }
}
