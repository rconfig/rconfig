<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get timezone', function () {
    $timezone = 'Europe/Dublin';

    $this->assertDatabaseHas('settings', [
        'id' => 1,
        'timezone' => $timezone,
    ]);

    $response = $this->get('/api/settings/timezone/1');
    $response->assertJson([
        'timezone' => $timezone,
    ]);
});

test('get timezone list', function () {
    $response = $this->get('/api/settings/get-timezone-list');
    $response->assertJsonFragment([
        'Pacific/Fiji' => '(GMT+12:00) Fiji',
    ]);
});

test('update timezone', function () {
    $timezone = 'Pacific/Midway';
    $response = $this->patch('/api/settings/timezone/1', ['timezone' => $timezone]);
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
    $this->assertDatabaseHas('settings', [
        'id' => 1,
        'timezone' => $timezone,
    ]);
    Artisan::call('config:cache');

    expect(Config::get('app.timezone'))->toEqual($timezone);
    expect(env('TIMEZONE'))->toEqual($timezone);

    // change back to Europe/Dublin
    Artisan::call('env:set TIMEZONE=Europe/Dublin');
    Artisan::call('config:cache');
    // cannot to a config:cache when testing
    expect(Config::get('app.timezone'))->toEqual('Europe/Dublin');
    expect(env('TIMEZONE'))->toEqual('Europe/Dublin');
});

test('update timezone writes clean identifier to env', function () {
    $timezone = 'Europe/Rome';
    $response = $this->patch('/api/settings/timezone/1', ['timezone' => $timezone]);
    $response->assertStatus(200);

    $this->assertStringContainsString('TIMEZONE=Europe/Rome', file_get_contents(app()->environmentFilePath()));

    // change back to Europe/Dublin
    Artisan::call('env:set TIMEZONE=Europe/Dublin');
});

test('update timezone busts dashboard sysinfo cache', function () {
    Cache::put('dashboard.sysinfo', ['timezone' => 'Europe/Dublin'], 600);
    expect(Cache::has('dashboard.sysinfo'))->toBeTrue();

    $response = $this->patch('/api/settings/timezone/1', ['timezone' => 'Pacific/Midway']);
    $response->assertStatus(200);

    expect(Cache::has('dashboard.sysinfo'))->toBeFalse();

    // change back to Europe/Dublin
    Artisan::call('env:set TIMEZONE=Europe/Dublin');
});

test('update timezone rejects invalid timezone', function () {
    $response = $this->patchJson('/api/settings/timezone/1', ['timezone' => 'Not/AZone']);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('timezone');
    $this->assertDatabaseMissing('settings', [
        'id' => 1,
        'timezone' => 'Not/AZone',
    ]);
});
