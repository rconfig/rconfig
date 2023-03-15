<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SettingsBannerControllerTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected $setting;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function get_login_banner()
    {
        $response = $this->get('/api/settings/banner/1');
        $response->assertJson([
            'login_banner' => 'Authorization message - You must be an authorized user to login and use this system.',
        ]);
    }

    /** @test */
    public function update_banner()
    {
        $new_banner = $this->faker->sentence;

        $response = $this->patch('/api/settings/banner/1', ['login_banner' => $new_banner]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'login_banner' => $new_banner,
        ]);
    }
}
