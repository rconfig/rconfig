<?php

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get login banner', function () {
    $response = $this->get('/api/settings/banner/1');
    $response->assertJson([
        'login_banner' => 'Authorization message - You must be an authorized user to login and use this system.',
    ]);
});

test('update banner', function () {
    $new_banner = $this->faker->sentence;

    $response = $this->patch('/api/settings/banner/1', ['login_banner' => $new_banner]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('settings', [
        'id' => 1,
        'login_banner' => $new_banner,
    ]);
});
