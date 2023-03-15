<?php

namespace Tests\Fasttests\OtherTests;

use App\Models\User;
use Tests\TestCase;

class HorizonBasicTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function horizon_dashboard_accessible()
    {
        $user = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($user)
            ->get('horizon');
        $response->assertStatus(200);
        $this->assertStringContainsString('<title>Horizon - rConfig6 - Network Configuration Management</title>', $response->getContent());
    }

    /** @test */
    public function horizon_timeout_value_can_be_changed()
    {
        // clear the config cache
        // dd(config('horizon.environments.production.supervisor-1.timeout'));
        $this->artisan('config:cache');
        // dd(config('horizon.environments.production.supervisor-1.timeout'));

        $this->assertEquals(60, config('horizon.environments.production.supervisor-1.timeout'));
        $this->assertEquals(60, config('horizon.environments.local.supervisor-1.timeout'));

        // change the timeout in the env file
        $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=120');
        $this->artisan('env:set HORIZON_PROD_TIMEOUT=120');

        $this->artisan('config:cache');
        // dd(config('horizon.environments.production.supervisor-1.timeout'));
        $this->assertEquals(120, config('horizon.environments.production.supervisor-1.timeout'));
        $this->assertEquals(120, config('horizon.environments.local.supervisor-1.timeout'));

        $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=60');
        $this->artisan('env:set HORIZON_PROD_TIMEOUT=60');

        $envExample = file_get_contents(base_path('.env.testing'));
        $this->assertStringContainsString('HORIZON_LOCAL_TIMEOUT=60', $envExample);
        $this->assertStringContainsString('HORIZON_PROD_TIMEOUT=60', $envExample);
    }

    /** @test */
    public function env_example_has_env_timeout_values()
    {
        $envExample = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('HORIZON_LOCAL_TIMEOUT=60', $envExample);
        $this->assertStringContainsString('HORIZON_PROD_TIMEOUT=60', $envExample);
    }
}
