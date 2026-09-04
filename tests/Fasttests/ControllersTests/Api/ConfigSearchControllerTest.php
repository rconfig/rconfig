<?php

use App\Models\Config;
use App\Models\User;
use App\Services\Config\Search\SearchStrategies\LatestSearchStrategyNew;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->strategy = new LatestSearchStrategyNew;
    Config::truncate();
});

test('search returns empty array when no search string provided', function () {
    DB::table('configs')->insert([
        'device_id' => 1001,
        'device_name' => 'router1',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
    ]);

    $result = $this->strategy->searchConfigurations([
        'search_string' => '',
        'command' => 'show run',
    ]);

    expect($result)->toBeEmpty();
});

test('search finds matches in configuration file', function () {
    DB::table('configs')->insert([
        'device_id' => 1001,
        'device_name' => 'router1',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
    ]);

    $result = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
    ]);

    expect($result)->not->toBeEmpty();
    expect($result)->toHaveCount(1);
    expect($result[0]['device_name'])->toEqual('router1');
    expect($result[0])->toHaveKey('matches');
    expect(count($result[0]['matches']))->toBeGreaterThan(0);
});

test('search respects case sensitivity', function () {
    DB::table('configs')->insert([
        'device_id' => 1001,
        'device_name' => 'router1',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
    ]);

    // Case-sensitive search (should find lowercase 'configuration')
    $caseSensitiveResult = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
        'ignore_case' => false,
    ]);

    // Case-sensitive search with uppercase (should not find anything if file has lowercase)
    $noMatchResult = $this->strategy->searchConfigurations([
        'search_string' => 'ZZZZNOTFOUND',
        'command' => 'show run',
        'ignore_case' => false,
    ]);

    // Case-insensitive search (should find regardless of case)
    $caseInsensitiveResult = $this->strategy->searchConfigurations([
        'search_string' => 'CONFIGURATION',
        'command' => 'show run',
        'ignore_case' => true,
    ]);

    expect($caseSensitiveResult)->not->toBeEmpty();
    expect($noMatchResult)->toBeEmpty();
    expect($caseInsensitiveResult)->not->toBeEmpty();
});

test('search filters by device name and category', function () {
    DB::table('configs')->insert([
        [
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
        ],
        [
            'device_id' => 1002,
            'device_name' => 'switch1',
            'device_category' => 'Switches',
            'command' => 'show run',
            'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
        ],
    ]);

    // Filter by device name
    $routerResult = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'device_name' => 'router',
        'command' => 'show run',
    ]);

    // Filter by category
    $switchResult = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'device_category' => 'Switches',
        'command' => 'show run',
    ]);

    expect($routerResult)->toHaveCount(1);
    expect($routerResult[0]['device_name'])->toEqual('router1');

    expect($switchResult)->toHaveCount(1);
    expect($switchResult[0]['device_name'])->toEqual('switch1');
});

test('search includes context lines before and after match', function () {
    DB::table('configs')->insert([
        'device_id' => 1001,
        'device_name' => 'router1',
        'device_category' => 'Routers',
        'command' => 'show run',
        'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
        'start_time' => now(),
        'latest_version' => 1,
        'created_at' => now(),
    ]);

    $resultWithoutContext = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
        'lines_before' => 0,
        'lines_after' => 0,
    ]);

    $resultWithContext = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
        'lines_before' => 2,
        'lines_after' => 2,
    ]);

    expect($resultWithoutContext)->not->toBeEmpty();
    expect($resultWithContext)->not->toBeEmpty();

    // Context result should have more lines
    $contextLines = explode("\n", $resultWithContext[0]['context']);
    $noContextLines = explode("\n", $resultWithoutContext[0]['context']);

    expect(count($contextLines))->toBeGreaterThan(count($noContextLines));
});

test('search filters by latest version only', function () {
    DB::table('configs')->insert([
        [
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
            'start_time' => now()->subDay(),
            'latest_version' => 0,
            'created_at' => now()->subDay(),
        ],
        [
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => base_path('tests/storage/configsearch/fake1.txt'),
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
        ],
    ]);

    // Without latest version filter (should return both)
    $allVersionsResult = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
    ]);

    // With latest version filter (should return only one)
    $latestOnlyResult = $this->strategy->searchConfigurations([
        'search_string' => 'configuration',
        'command' => 'show run',
        'latest_version_only' => true,
    ]);

    expect($allVersionsResult)->toHaveCount(2);
    expect($latestOnlyResult)->toHaveCount(1);
});

afterEach(function () {
    Config::truncate();
});
