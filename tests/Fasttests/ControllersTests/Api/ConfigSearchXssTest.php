<?php

use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Contract cover for the stored XSS reported in rconfig/rconfig#368.
 *
 * The defect was in the front end: `PeekConfigSearchMatchesDialog.vue` rendered the
 * search match context through `v-html` without escaping it. The fix belongs at the
 * render layer, not here, because configuration text has to reach exports, diffs and
 * API clients byte for byte.
 *
 * These tests pin that division: the endpoint returns device configuration verbatim,
 * and escaping is proven at the sink by
 * `resources/js/pages/Shared/Dialogs/__tests__/PeekConfigSearchMatchesDialog.spec.ts`.
 * If someone ever "fixes" this by escaping server side, these fail and point at the
 * spec that already covers the real defect.
 */
beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    Config::query()->delete();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * Seed a config row pointing at the poisoned fixture.
 *
 * The fixture lives under `tests/storage/` because `Config`'s `deleted` hook skips
 * deleting files on that path, so it survives the cleanup above.
 */
function seedPoisonedConfig(): void
{
    DB::table('configs')->insert([
        'device_id' => 1001,
        'device_name' => 'sw-poisoned',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/xss.txt'),
        'config_filename' => 'xss.txt',
        'config_filesize' => 200,
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('match context returns configuration markup verbatim for the front end to escape', function () {
    seedPoisonedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['id' => 'criterion-1', 'term' => 'hostname']],
        'ignore_case' => true,
        'lines_before' => 5,
        'lines_after' => 5,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.0.device_name', 'sw-poisoned');

    $context = $response->json('data.0.matches.0.context');

    expect($context)->toBeArray();
    expect($context)->toContain('<img src=x onerror="window.__xss_368=1">');
    expect($context)->toContain('<script>window.__xss_368=1</script>');
});

test('preview match line text is returned verbatim', function () {
    seedPoisonedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['id' => 'criterion-1', 'term' => 'description']],
        'ignore_case' => true,
    ]);

    $response->assertOk();
    expect($response->json('data.0.preview_match.line_text'))->toBe(' description <svg/onload=alert(1)>');
});

test('a payload outside the context window is not returned', function () {
    seedPoisonedConfig();

    // The payload sits on lines 5 and 6; a zero-line window around line 2 must not reach it.
    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['id' => 'criterion-1', 'term' => 'hostname']],
        'ignore_case' => true,
        'lines_before' => 0,
        'lines_after' => 0,
    ]);

    $response->assertOk();
    expect($response->json('data.0.matches.0.context'))->toBe(['hostname sw-poisoned']);
});
