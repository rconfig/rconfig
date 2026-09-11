<?php

use App\Models\User;

test('horizon dashboard accessible', function () {
    $user = User::factory()->create(['role' => 'Admin']);

    $response = $this->actingAs($user)->get('horizon');
    $response->assertStatus(200);
    expect($response->getContent())->toContain(config('app.name'));
});

test('horizon timeout value can be changed', function () {
    // clear the config cache
    $this->artisan('config:clear');
    $this->artisan('config:cache');

    expect(config('horizon.environments.production.HorizonOne.timeout'))->toEqual(120);
    expect(config('horizon.environments.local.HorizonOne.timeout'))->toEqual(120);

    // change the timeout in the env file
    $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=320');
    $this->artisan('env:set HORIZON_PROD_TIMEOUT=320');

    $this->artisan('config:cache');
    expect(config('horizon.environments.production.HorizonOne.timeout'))->toEqual(320);
    expect(config('horizon.environments.local.HorizonOne.timeout'))->toEqual(320);

    $this->artisan('env:set HORIZON_LOCAL_TIMEOUT=120');
    $this->artisan('env:set HORIZON_PROD_TIMEOUT=120');

    $envExample = file_get_contents(base_path('.env.testing'));
    expect($envExample)->toContain('HORIZON_LOCAL_TIMEOUT=120');
    expect($envExample)->toContain('HORIZON_PROD_TIMEOUT=120');
});

test('env example has env timeout values', function () {
    $envExample = file_get_contents(base_path('.env.example'));
    expect($envExample)->toContain('HORIZON_LOCAL_TIMEOUT=120');
    expect($envExample)->toContain('HORIZON_PROD_TIMEOUT=120');
});
