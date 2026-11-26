<?php

use App\Models\SettingsPurge;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->beginTransaction();
    Cache::flush();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('guests cannot read or change purge settings', function () {
    auth()->logout();
    $id = SettingsPurge::where('target', 'tracked_jobs')->value('id');

    $this->getJson('/api/settings/purge')->assertUnauthorized();
    $this->patchJson("/api/settings/purge/{$id}", ['retention_days' => 1, 'options' => ['enabled' => true]])->assertUnauthorized();

    expect(SettingsPurge::find($id)->isEnabled())->toBeFalse();
});

test('lists the seeded purge settings with table stats', function () {
    $response = $this->getJson('/api/settings/purge')->assertOk();

    $response->assertJsonPath('total', 6)
        ->assertJsonPath('per_page', 25)
        ->assertJsonStructure(['data' => [['id', 'target', 'retention_days', 'options' => ['batch_size', 'cleanup_frequency', 'enabled'], 'total_rows', 'total_bytes', 'total_bytes_hum']]]);

    expect(collect($response->json('data'))->pluck('target')->all())
        ->toBe(['activity_log', 'configs', 'failed_jobs', 'job_batches', 'jobs', 'tracked_jobs']);
});

test('filters by search term and by enabled only', function () {
    SettingsPurge::where('target', 'jobs')->first()->update(['options' => ['batch_size' => 2000, 'cleanup_frequency' => 'daily', 'enabled' => true]]);

    $search = $this->getJson('/api/settings/purge?filter[q]=job')->assertOk();
    $enabledOnly = $this->getJson('/api/settings/purge?filter[enabled_only]=1')->assertOk();

    expect(collect($search->json('data'))->pluck('target')->all())->toBe(['failed_jobs', 'job_batches', 'jobs', 'tracked_jobs'])
        ->and(collect($enabledOnly->json('data'))->pluck('target')->all())->toBe(['jobs']);
});

test('sorts by retention days and by enabled state', function () {
    SettingsPurge::where('target', 'jobs')->first()->update(['options' => ['batch_size' => 2000, 'cleanup_frequency' => 'daily', 'enabled' => true]]);

    $byRetention = $this->getJson('/api/settings/purge?sort=-retention_days')->assertOk();
    $byEnabled = $this->getJson('/api/settings/purge?sort=-enabled')->assertOk();

    expect($byRetention->json('data.0.target'))->toBe('configs')
        ->and($byEnabled->json('data.0.target'))->toBe('jobs');
});

test('paginates', function () {
    $this->getJson('/api/settings/purge?perPage=2&page=2')->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('current_page', 2)
        ->assertJsonPath('last_page', 3);
});

test('shows one purge setting', function () {
    $setting = SettingsPurge::where('target', 'activity_log')->first();

    $this->getJson("/api/settings/purge/{$setting->id}")->assertOk()
        ->assertJsonPath('target', 'activity_log')
        ->assertJsonPath('retention_days', 90);

    $this->getJson('/api/settings/purge/99999999')->assertNotFound();
});

test('updates a purge setting sent as a json string, as the settings page does', function () {
    $setting = SettingsPurge::where('target', 'tracked_jobs')->first();

    $this->patchJson("/api/settings/purge/{$setting->id}", [
        'target' => 'users',
        'retention_days' => 45,
        'options' => json_encode(['batch_size' => 250, 'cleanup_frequency' => 'weekly', 'enabled' => true]),
    ])->assertOk()->assertJson(['success' => true, 'data' => ['id' => $setting->id]]);

    expect($setting->fresh())
        ->target->toBe('tracked_jobs')
        ->retention_days->toBe(45)
        ->options->toBe(['batch_size' => 250, 'cleanup_frequency' => 'weekly', 'enabled' => true])
        ->and($setting->fresh()->isEnabled())->toBeTrue();
});

test('a partial options update keeps the config retention extras', function () {
    $setting = SettingsPurge::where('target', 'configs')->first();

    $this->patchJson("/api/settings/purge/{$setting->id}", [
        'retention_days' => 365,
        'options' => ['enabled' => true, 'unknown_key' => 'dropped'],
    ])->assertOk();

    expect($setting->fresh()->options)->toBe([
        'batch_size' => 1000,
        'cleanup_frequency' => 'daily',
        'enabled' => true,
        'min_versions_per_device_command' => 3,
        'non_downloaded_retention_days' => 30,
    ]);
});

test('rejects invalid values', function (array $payload, string $errorKey) {
    $setting = SettingsPurge::where('target', 'tracked_jobs')->first();

    $this->patchJson("/api/settings/purge/{$setting->id}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorKey);

    expect($setting->fresh()->retention_days)->toBe(30)
        ->and($setting->fresh()->isEnabled())->toBeFalse();
})->with([
    'zero retention' => [['retention_days' => 0, 'options' => ['enabled' => true]], 'retention_days'],
    'missing retention' => [['options' => ['enabled' => true]], 'retention_days'],
    'options not json' => [['retention_days' => 10, 'options' => 'not-json'], 'options'],
    'zero batch size' => [['retention_days' => 10, 'options' => ['batch_size' => 0]], 'options.batch_size'],
    'unknown frequency' => [['retention_days' => 10, 'options' => ['cleanup_frequency' => 'hourly']], 'options.cleanup_frequency'],
    'enabled not boolean' => [['retention_days' => 10, 'options' => ['enabled' => 'yes please']], 'options.enabled'],
    'zero minimum versions' => [['retention_days' => 10, 'options' => ['min_versions_per_device_command' => 0]], 'options.min_versions_per_device_command'],
]);

test('updating an unknown purge setting is a 404', function () {
    $this->patchJson('/api/settings/purge/99999999', ['retention_days' => 10, 'options' => ['enabled' => true]])->assertNotFound();
});

test('purge settings cannot be created or deleted through the api', function () {
    $id = SettingsPurge::where('target', 'tracked_jobs')->value('id');

    $this->postJson('/api/settings/purge', ['target' => 'users', 'retention_days' => 1])->assertStatus(405);
    $this->deleteJson("/api/settings/purge/{$id}")->assertStatus(405);

    expect(SettingsPurge::count())->toBe(6);
});
