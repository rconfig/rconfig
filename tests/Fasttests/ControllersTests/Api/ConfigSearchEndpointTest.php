<?php

use App\Models\Category;
use App\Models\Command;
use App\Models\Config;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Clear any existing configs within the transaction so counts are deterministic.
    Config::query()->delete();
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * @param  array<string, mixed>  $overrides
 */
function seedConfig(array $overrides = []): void
{
    DB::table('configs')->insert(array_merge([
        'device_id' => 1001,
        'device_name' => 'router1',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
        'config_filename' => 'fake1.txt',
        'config_filesize' => 110,
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

test('endpoint requires at least one search term', function () {
    seedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => '']],
    ]);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

test('endpoint returns matches for a single term', function () {
    seedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['id' => 'criterion-1', 'term' => 'configuration']],
        'ignore_case' => true,
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.device_name', 'router1');

    $row = $response->json('data.0');
    expect($row['match_count'])->toBe(2);
    // "configuration" appears on two lines
    expect($row['preview_match'])->not->toBeNull();
    expect($row['preview_match']['matched_terms'][0])->toBe('configuration');
});

test('all terms mode requires every term to match', function () {
    seedConfig();

    $bothPresent = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration'], ['term' => 'interface']],
        'criteria_mode' => 'all',
        'ignore_case' => true,
    ]);
    $bothPresent->assertOk();
    $bothPresent->assertJsonCount(1, 'data');

    $oneMissing = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration'], ['term' => 'ZZZNOTPRESENT']],
        'criteria_mode' => 'all',
        'ignore_case' => true,
    ]);
    $oneMissing->assertOk();
    $oneMissing->assertJsonCount(0, 'data');
});

test('any term mode matches when one term is present', function () {
    seedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration'], ['term' => 'ZZZNOTPRESENT']],
        'criteria_mode' => 'any',
        'ignore_case' => true,
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('results are paginated with meta', function () {
    seedConfig(['device_id' => 1001, 'device_name' => 'router1']);
    seedConfig(['device_id' => 1002, 'device_name' => 'router2']);
    seedConfig(['device_id' => 1003, 'device_name' => 'router3']);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'perPage' => 2,
        'page' => 1,
    ]);

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    $response->assertJsonPath('meta.total', 3);
    $response->assertJsonPath('meta.last_page', 2);
    $response->assertJsonPath('meta.per_page', 2);
    $response->assertJsonPath('meta.current_page', 1);
});

test('latest version only filter is applied', function () {
    seedConfig(['device_id' => 1001, 'latest_version' => 0, 'created_at' => now()->subDay()]);
    seedConfig(['device_id' => 1001, 'latest_version' => 1, 'created_at' => now()]);

    $all = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
    ]);
    $all->assertOk();
    $all->assertJsonCount(2, 'data');

    $latest = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'latest_version_only' => true,
    ]);
    $latest->assertOk();
    $latest->assertJsonCount(1, 'data');
});

test('limit caps results and flags limit reached', function () {
    seedConfig(['device_id' => 1001]);
    seedConfig(['device_id' => 1002]);
    seedConfig(['device_id' => 1003]);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'limit' => 2,
    ]);

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
    $response->assertJsonPath('meta.limit', 2);
    $response->assertJsonPath('meta.limit_reached', true);
    $response->assertJsonPath('meta.total', 2);
});

test('date range filters by created at', function () {
    seedConfig(['device_id' => 1001, 'created_at' => now()]);
    seedConfig(['device_id' => 1002, 'created_at' => now()->subYear()]);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'dateFrom' => now()->subDays(7)->toDateString(),
        'dateTo' => now()->toDateString(),
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.device_id', 1001);
});

test('command id filter resolves to command string', function () {
    $command = Command::factory()->create(['command' => 'show run']);
    seedConfig(['device_id' => 1001, 'command' => 'show run']);
    seedConfig(['device_id' => 1002, 'command' => 'show version']);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'commands' => [$command->id],
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.command', 'show run');
});

test('unknown command filter returns empty', function () {
    seedConfig();

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'commands' => [999999],
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(0, 'data');
});

test('case sensitive search respects case', function () {
    seedConfig();

    $sensitiveMiss = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'CONFIGURATION']],
        'case_sensitive' => true,
    ]);
    $sensitiveMiss->assertOk();
    $sensitiveMiss->assertJsonCount(0, 'data');

    $insensitiveHit = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'CONFIGURATION']],
        'case_sensitive' => false,
    ]);
    $insensitiveHit->assertOk();
    $insensitiveHit->assertJsonCount(1, 'data');
});

test('devices filter limits to selected devices', function () {
    seedConfig(['device_id' => 1001]);
    seedConfig(['device_id' => 1002]);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'devices' => [1001],
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.device_id', 1001);
});

test('categories filter uses device relationship', function () {
    $device = Device::factory()->create();
    $category = Category::factory()->create();
    $device->category()->attach($category->id);

    // Config whose device belongs to the category.
    seedConfig(['device_id' => $device->id]);

    // Config whose device has no matching category (no device row at all).
    seedConfig(['device_id' => 1002]);

    $response = $this->postJson('/api/configs/search', [
        'criteria' => [['term' => 'configuration']],
        'ignore_case' => true,
        'categories' => [$category->id],
    ]);

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.device_id', $device->id);
});

test('legacy search string returns legacy format', function () {
    seedConfig();

    $response = $this->postJson('/api/configs/search', [
        'search_string' => 'configuration',
        'ignore_case' => true,
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('data.search_term', 'configuration');
    expect($response->json('data.results'))->toBeArray();
    expect($response->json('data.results'))->toHaveCount(1);
});
