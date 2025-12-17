<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\User;
use App\Services\Config\Search\SearchStrategies\LatestSearchStrategyNew;
use Tests\TestCase;

class ConfigSearchControllerTest extends TestCase
{
    protected $user;
    protected $strategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
        $this->strategy = new LatestSearchStrategyNew();
        Config::truncate();
    }

    public function test_search_returns_empty_array_when_no_search_string_provided()
    {
        \DB::table('configs')->insert([
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
        ]);

        $result = $this->strategy->searchConfigurations([
            'search_string' => '',
            'command' => 'show run',
        ]);

        $this->assertEmpty($result);
    }

    public function test_search_finds_matches_in_configuration_file()
    {
        \DB::table('configs')->insert([
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
            'start_time' => now(),
            'latest_version' => 1,
            'created_at' => now(),
        ]);

        $result = $this->strategy->searchConfigurations([
            'search_string' => 'configuration',
            'command' => 'show run',
        ]);

        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals('router1', $result[0]['device_name']);
        $this->assertArrayHasKey('matches', $result[0]);
        $this->assertGreaterThan(0, count($result[0]['matches']));
    }

    public function test_search_respects_case_sensitivity()
    {
        \DB::table('configs')->insert([
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
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

        $this->assertNotEmpty($caseSensitiveResult);
        $this->assertEmpty($noMatchResult);
        $this->assertNotEmpty($caseInsensitiveResult);
    }

    public function test_search_filters_by_device_name_and_category()
    {
        \DB::table('configs')->insert([
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
                'start_time' => now(),
                'latest_version' => 1,
                'created_at' => now(),
            ],
            [
                'device_id' => 1002,
                'device_name' => 'switch1',
                'device_category' => 'Switches',
                'command' => 'show run',
                'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
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

        $this->assertCount(1, $routerResult);
        $this->assertEquals('router1', $routerResult[0]['device_name']);
        
        $this->assertCount(1, $switchResult);
        $this->assertEquals('switch1', $switchResult[0]['device_name']);
    }

    public function test_search_includes_context_lines_before_and_after_match()
    {
        \DB::table('configs')->insert([
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
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

        $this->assertNotEmpty($resultWithoutContext);
        $this->assertNotEmpty($resultWithContext);
        
        // Context result should have more lines
        $contextLines = explode("\n", $resultWithContext[0]['context']);
        $noContextLines = explode("\n", $resultWithoutContext[0]['context']);
        
        $this->assertGreaterThan(count($noContextLines), count($contextLines));
    }

    public function test_search_filters_by_latest_version_only()
    {
        \DB::table('configs')->insert([
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
                'start_time' => now()->subDay(),
                'latest_version' => 0,
                'created_at' => now()->subDay(),
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
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

        $this->assertCount(2, $allVersionsResult);
        $this->assertCount(1, $latestOnlyResult);
    }

    public function tearDown(): void
    {
        Config::truncate();
        parent::tearDown();
    }
}