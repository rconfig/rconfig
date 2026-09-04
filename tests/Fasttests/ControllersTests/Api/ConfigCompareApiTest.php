<?php

use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

test('show change by current config id returns the diff', function () {
    $previous = Config::factory()->create(['device_id' => 555001, 'command' => 'show run', 'download_status' => 1]);
    $current = Config::factory()->create(['device_id' => 555001, 'command' => 'show run', 'download_status' => 1]);

    $change = ConfigChange::create([
        'current_config_id' => $current->id,
        'previous_config_id' => $previous->id,
        'config_version' => 2,
        'config_change_type' => 'changed',
        'config_diff' => '<div class="diff-wrapper">diff body</div>',
        'change_trigger' => 'pull',
    ]);

    $response = $this->getJson('/api/config-changes/current-config/' . $current->id);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $change->id,
            'config_change_type' => 'changed',
            'config_version' => 2,
        ]);
});

test('config history lists versions for device and command', function () {
    Config::factory()->create(['device_id' => 555002, 'command' => 'show run', 'download_status' => 1, 'config_version' => 1, 'latest_version' => 0]);
    Config::factory()->create(['device_id' => 555002, 'command' => 'show run', 'download_status' => 1, 'config_version' => 2, 'latest_version' => 1]);

    $response = $this->getJson('/api/configs/config-history/555002/show run?perPage=10');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('compare options can be read updated and reset', function () {
    $read = $this->getJson('/api/settings/compare-options');
    $read->assertStatus(200);

    $update = $this->patchJson('/api/settings/compare-options/1', [
        'context' => 5,
        'lengthLimit' => 30000,
        'ignoreCase' => true,
        'ignoreLineEnding' => false,
        'ignoreWhitespace' => true,
        'config_compare_exclusion_file' => "// test\n#[global]\n/^foo.*$/m",
    ]);
    $update->assertStatus(200);

    $setting = Setting::find(1);
    expect($setting->config_compare_settings['context'])->toBe(5);
    expect($setting->config_compare_settings['ignoreWhitespace'])->toBeTrue();
    $this->assertStringContainsString('foo', $setting->config_compare_exclusion_file);

    $default = $this->getJson('/api/settings/compare-options/default-template');
    $default->assertStatus(200);
});
