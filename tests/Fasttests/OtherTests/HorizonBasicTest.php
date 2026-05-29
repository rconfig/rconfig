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

    public function test_horizon_dashboard_accessible()
    {
        $user = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($user)->get('horizon');
        $response->assertStatus(200);
        $this->assertStringContainsString(config('app.name'), $response->getContent());
    }

    public function test_horizon_timeout_value_can_be_changed()
    {
        // clear the config cache
        $this->artisan('config:clear');
        $this->artisan('config:cache');

        $this->assertEquals(120, config('horizon.environments.production.HorizonOne.timeout'));
        $this->assertEquals(120, config('horizon.environments.local.HorizonOne.timeout'));

        // change the timeout in the env file
        $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=320');
        $this->artisan('env:set HORIZON_PROD_TIMEOUT=320');

        $this->artisan('config:cache');
        $this->assertEquals(320, config('horizon.environments.production.HorizonOne.timeout'));
        $this->assertEquals(320, config('horizon.environments.local.HorizonOne.timeout'));

        $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=120');
        $this->artisan('env:set HORIZON_PROD_TIMEOUT=120');

        $envExample = file_get_contents(base_path('.env.testing'));
        $this->assertStringContainsString('HORIZON_LOCAL_TIMEOUT=120', $envExample);
        $this->assertStringContainsString('HORIZON_PROD_TIMEOUT=120', $envExample);
    }

    public function test_env_example_has_env_timeout_values()
    {
        $envExample = file_get_contents(base_path('.env.example'));
        $this->assertStringContainsString('HORIZON_LOCAL_TIMEOUT=120', $envExample);
        $this->assertStringContainsString('HORIZON_PROD_TIMEOUT=120', $envExample);
    }
}
