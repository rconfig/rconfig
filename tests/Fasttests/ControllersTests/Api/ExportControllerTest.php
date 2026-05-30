<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Http\Controllers\Api\ExportController;
use App\Models\User;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    /** @var User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }

    public function test_it_can_get_the_list_of_exportable_tables()
    {
        $response = $this->get('/api/settings/export/list-tables');
        $response->assertStatus(200);
        $this->assertArrayHasKey('tables', $response->json()['data']);
        $this->assertContains('activity_log', $response->json()['data']['tables']);
        $this->assertContains('devices', $response->json()['data']['tables']);
        $this->assertContains('users', $response->json()['data']['tables']);
        $this->assertNotContains('migrations', $response->json()['data']['tables']);
        $this->assertNotContains('sessions', $response->json()['data']['tables']);
        $this->assertNotContains('jobs', $response->json()['data']['tables']);

        // check that the list of tables matches the expected list based on the excluded tables
        $expected = array_values(array_diff(
            ExportController::listAllBaseTables(),
            ExportController::EXCLUDED_TABLES
        ));
        $response->assertJsonCount(count($expected), 'data.tables');
        $this->assertEqualsCanonicalizing($expected, $response->json('data.tables'));
    }

    public function test_it_can_export_a_table()
    {
        // https://docs.laravel-excel.com/3.1/exports/testing.html#testing-downloads
        $table = 'users';

        $response = $this->get('/api/settings/export/get-table/' . $table)
            ->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'downloadLink',
                'downloadUrl',
                'filename',
            ],
        ]);
        $response->assertJsonFragment([
            'downloadUrl' => '/download-export?filename=users.csv&type=export',
            'filename' => 'users.csv',
        ]);
    }
}
