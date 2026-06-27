<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Category;
use App\Models\Command;
use App\Models\Config;
use App\Models\Device;
use App\Models\User;
use Tests\TestCase;

class ConfigSearchEndpointTest extends TestCase
{
    protected $user;
    protected string $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->fixture = base_path('tests/storage/configsearch/fake1.txt');
        // Clear any existing configs within the transaction so counts are deterministic.
        Config::query()->delete();
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function seedConfig(array $overrides = []): void
    {
        \DB::table('configs')->insert(array_merge([
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => $this->fixture,
            'config_filename' => 'fake1.txt',
            'config_filesize' => 110,
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_endpoint_requires_at_least_one_search_term(): void
    {
        $this->seedConfig();

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => '']],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_endpoint_returns_matches_for_a_single_term(): void
    {
        $this->seedConfig();

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['id' => 'criterion-1', 'term' => 'configuration']],
            'ignore_case' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.device_name', 'router1');

        $row = $response->json('data.0');
        $this->assertSame(2, $row['match_count']); // "configuration" appears on two lines
        $this->assertNotNull($row['preview_match']);
        $this->assertSame('configuration', $row['preview_match']['matched_terms'][0]);
    }

    public function test_all_terms_mode_requires_every_term_to_match(): void
    {
        $this->seedConfig();

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
    }

    public function test_any_term_mode_matches_when_one_term_is_present(): void
    {
        $this->seedConfig();

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration'], ['term' => 'ZZZNOTPRESENT']],
            'criteria_mode' => 'any',
            'ignore_case' => true,
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_results_are_paginated_with_meta(): void
    {
        $this->seedConfig(['device_id' => 1001, 'device_name' => 'router1']);
        $this->seedConfig(['device_id' => 1002, 'device_name' => 'router2']);
        $this->seedConfig(['device_id' => 1003, 'device_name' => 'router3']);

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
    }

    public function test_latest_version_only_filter_is_applied(): void
    {
        $this->seedConfig(['device_id' => 1001, 'latest_version' => 0, 'created_at' => now()->subDay()]);
        $this->seedConfig(['device_id' => 1001, 'latest_version' => 1, 'created_at' => now()]);

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
    }

    public function test_limit_caps_results_and_flags_limit_reached(): void
    {
        $this->seedConfig(['device_id' => 1001]);
        $this->seedConfig(['device_id' => 1002]);
        $this->seedConfig(['device_id' => 1003]);

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
    }

    public function test_date_range_filters_by_created_at(): void
    {
        $this->seedConfig(['device_id' => 1001, 'created_at' => now()]);
        $this->seedConfig(['device_id' => 1002, 'created_at' => now()->subYear()]);

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration']],
            'ignore_case' => true,
            'dateFrom' => now()->subDays(7)->toDateString(),
            'dateTo' => now()->toDateString(),
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.device_id', 1001);
    }

    public function test_command_id_filter_resolves_to_command_string(): void
    {
        $command = Command::factory()->create(['command' => 'show run']);
        $this->seedConfig(['device_id' => 1001, 'command' => 'show run']);
        $this->seedConfig(['device_id' => 1002, 'command' => 'show version']);

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration']],
            'ignore_case' => true,
            'commands' => [$command->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.command', 'show run');
    }

    public function test_unknown_command_filter_returns_empty(): void
    {
        $this->seedConfig();

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration']],
            'ignore_case' => true,
            'commands' => [999999],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(0, 'data');
    }

    public function test_case_sensitive_search_respects_case(): void
    {
        $this->seedConfig();

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
    }

    public function test_devices_filter_limits_to_selected_devices(): void
    {
        $this->seedConfig(['device_id' => 1001]);
        $this->seedConfig(['device_id' => 1002]);

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration']],
            'ignore_case' => true,
            'devices' => [1001],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.device_id', 1001);
    }

    public function test_categories_filter_uses_device_relationship(): void
    {
        $device = Device::factory()->create();
        $category = Category::factory()->create();
        $device->category()->attach($category->id);

        // Config whose device belongs to the category.
        $this->seedConfig(['device_id' => $device->id]);
        // Config whose device has no matching category (no device row at all).
        $this->seedConfig(['device_id' => 1002]);

        $response = $this->postJson('/api/configs/search', [
            'criteria' => [['term' => 'configuration']],
            'ignore_case' => true,
            'categories' => [$category->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.device_id', $device->id);
    }

    public function test_legacy_search_string_returns_legacy_format(): void
    {
        $this->seedConfig();

        $response = $this->postJson('/api/configs/search', [
            'search_string' => 'configuration',
            'ignore_case' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.search_term', 'configuration');
        $this->assertIsArray($response->json('data.results'));
        $this->assertCount(1, $response->json('data.results'));
    }
}
