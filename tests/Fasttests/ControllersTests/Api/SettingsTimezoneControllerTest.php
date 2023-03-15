<?php

namespace Tests\Fasttests\ControllersTests\Api;

use Tests\TestCase;

class SettingsTimezoneControllerTest extends TestCase
{
    protected $user;

    protected $setting;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function get_timezone()
    {
        $timezone = 'Europe/Dublin';

        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'timezone' => $timezone,
        ]);

        $response = $this->get('/api/settings/timezone/1');
        $response->assertJson([
            'timezone' => $timezone,
        ]);
    }

    /** @test */
    public function get_timezone_list()
    {
        $response = $this->get('/api/settings/get-timezone-list');
        $response->assertJsonFragment([
            'Pacific/Fiji' => '(GMT+12:00) Fiji',
        ]);
    }

    /** @test */
    public function update_timezone()
    {
        $timezone = 'Pacific/Midway';
        $response = $this->patch('/api/settings/timezone/1', ['timezone' => $timezone]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('settings', [
            'id' => 1,
            'timezone' => $timezone,
        ]);
        \Artisan::call('config:cache');

        $this->assertEquals($timezone, \Config::get('app.timezone'));
        $this->assertEquals($timezone, env('TIMEZONE'));

        // change back to Europe/Dublin
        \Artisan::call('env:set TIMEZONE=Europe/Dublin');
        \Artisan::call('config:cache'); // cannot to a config:cache when testing
        $this->assertEquals('Europe/Dublin', \Config::get('app.timezone'));
        $this->assertEquals('Europe/Dublin', env('TIMEZONE'));
    }
}
