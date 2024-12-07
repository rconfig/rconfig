<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\User;
use App\Services\Config\Search\SearchStrategies\LatestSearchStrategyNew;
use Tests\TestCase;

class ConfigSearchControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        /** @var mixed $this->user */
        $this->actingAs($this->user, 'api');
        Config::truncate();
    }

    public function test_a_search_validation_errors()
    {
        $response = $this->json('post', '/api/configs/search', ['category' => null]);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['search_string', 'command']);
    }

    public function test_search_configurations_returns_matches_for_LatestSearchStrategyNew()
    {
        // Setup - insert dummy records into the 'configs' table
        $config = \DB::table('configs')->insert([
            'id' => 1,
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
            'start_time' => '2024-09-12 00:00:00',
            'latest_version' => 1,
            'end_time' => '2024-09-12 01:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Call the function with mock parameters
        $result = (new LatestSearchStrategyNew())->searchConfigurations('router1', 'Routers', 'show run', 'configuration', 0, 0, true, '2024-09-01', '2024-09-12', false);

        // Assert the structure of the result
        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result['last_page']);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals(10, $result['per_page']);
        $data = $result['data'];
        $this->assertEquals(1, $data[0]['id']);
        $this->assertEquals('router1', $data[0]['device_name']);
        $this->assertEquals(5, $data[0]['matches'][1]['line_number']);
        $this->assertEquals('Another configuration line', $data[0]['matches'][1]['context']);
    }

    public function test_search_returns_empty_array()
    {
        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['command' => 'show run', 'search_string' => 'snmp',]);

        $response->assertJson([]);
    }

    public function test_search_returns_valid_result_on_api_endpoint()
    {
        $config = \DB::table('configs')->insert([
            'id' => 1,
            'device_id' => 1001,
            'device_name' => 'router1',
            'device_category' => 'Routers',
            'command' => 'show run',
            'config_location' => '/var/www/html/rconfig/tests/storage/configsearch/fake1.txt',
            'start_time' => '2024-09-12 00:00:00',
            'end_time' => '2024-09-12 01:00:00',
            'latest_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['device_name' => 'outer1', 'command' => 'show run', 'search_string' => 'configuration', 'lines_before' => 0, 'lines_after' => 0, 'latest_version_only' => 'true', 'start_date' => '2024-09-01', 'end_date' => '2024-09-12', 'ignore_case' => 'false', 'page' => 1, 'per_page' => 10]);
        $response->assertStatus(200);

        $response->assertJsonCount(5);
        $this->assertCount(2, $response->json()['data'][0]['matches']);

        $response->assertJsonStructure([
            'last_page',
            'total',
            'per_page',
            'data' => [
                '*' => [
                    'id',
                    'device_name',
                    'device_category',
                    'file',
                    'config_filesize',
                    'config_date',
                    'matches' => [
                        '*' => [
                            'line_number',
                            'context',
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function tearDown(): void
    {
        Config::truncate();
        parent::tearDown();
    }
}
