<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Tests\TestCase;

class SystemHealthControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function sys_environment_latest()
    {
        $response = $this->json('get', '/api/dashboard/health-latest');

        $response->assertStatus(200);
        $this->assertCount(8, $response->json()['data']);
    }
}
