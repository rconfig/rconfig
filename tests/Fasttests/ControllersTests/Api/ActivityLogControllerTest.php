<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\ActivityLog;
use App\Models\ActivityLogArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
    use withFaker;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        ActivityLog::truncate();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_get_all_logs()
    {
        $logs = collect(range(1, 100))
            ->map(fn () => [
                'description' => $this->faker->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        // Bulk insert instead of individual activity() calls
        DB::table('activity_log')->insert($logs->toArray());

        $response = $this->getJson('/api/activitylogs?page=1&perPage=100');

        $response->assertStatus(200)
            ->assertJsonCount(100, 'data');
    }

    public function test_get_all_logs_with_device_id_filter()
    {
        // Keep the original approach but optimize with transaction
        DB::transaction(function () {
            for ($i = 0; $i < 20; $i++) {
                $msg = 'Authenticating user  (stephen) against database.';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg . $i, 'authentication');
            }
            for ($i = 0; $i < 20; $i++) {
                activityLogIt(__CLASS__, __FUNCTION__, 'info', 'SomeMesgForTesting' . $i, 'connection', '', 1001, 'download');
            }
            for ($i = 0; $i < 20; $i++) {
                activityLogIt(__CLASS__, __FUNCTION__, 'info', 'SomeMesgForTesting' . $i, 'connection', '', 1002, 'download');
            }
        });

        $this->assertEquals(60, ActivityLog::count());

        // Fixed URL syntax
        $response = $this->get('/api/activitylogs?page=1&perPage=100&filter[device_id]=1001');

        $this->assertEquals(20, count($response['data'])); // Should be 20, not 60
        $response->assertStatus(200);
    }

    public function test_create_log_entry()
    {
        $log = activity()->log($this->faker->sentence());

        $this->assertDatabaseHas('activity_log', [
            'description' => $log->description,
        ]);
    }

    public function test_show_single_log_entry()
    {
        $log = activity()->log($this->faker->sentence());

        $response = $this->get('/api/activitylogs/' . $log->id);

        $response->assertJsonFragment([
            'description' => $log->description,
        ]);
    }

    public function test_test_log_it_helper_function()
    {
        // logIt($class, $function, $log_name, $description, $event_type, $device_name = null, $device_id = null, $connection_category = null, $connection_ids = null );
        $logMsg = $this->faker->sentence;
        $log = activityLogIt(__CLASS__, __FUNCTION__, 'info', $logMsg, 'downloader');
        $this->assertDatabaseHas('activity_log', [
            'description' => $logMsg,
        ]);
    }

    public function test_get_last5_logs_by_deviceid()
    {
        $device_name = $this->faker->name;
        $device_id = $this->faker->randomDigit;
        for ($i = 0; $i < 100; $i++) {
            activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
        }

        $response = $this->get('/api/activitylogs/last5/' . $device_id);

        $response->assertSee($device_id);
        $response->assertJsonCount(5);
        $response->assertStatus(200);
        $this->assertDatabaseHas('activity_log', [
            'device_id' => $device_id,
        ]);
    }

    public function test_get_log_stats_by_deviceid()
    {
        $device_name = $this->faker->name;
        $device_id = $this->faker->randomDigit;
        for ($i = 0; $i < 100; $i++) {
            activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
        }

        $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
        $response->assertStatus(200);
        $response->assertSee('log_name');
    }


    public function test_clear_logs_by_deviceid()
    {
        $device_name = $this->faker->name;
        $device_id = $this->faker->randomDigit;
        for ($i = 0; $i < 100; $i++) {
            activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
        }

        $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());

        $response = $this->get('/api/activitylogs/clear-logs/' . $device_id);
        $response->assertStatus(200);

        $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
        $response->assertStatus(200);
        $this->assertCount(0, $response->json());
    }
    public function test_delete_log_entry()
    {
        $descr = $this->faker->sentence;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $descr, 'downloader');
        $this->assertDatabaseHas('activity_log', [
            'description' => $descr,
        ]);

        $log = ActivityLog::latest()->first();
        $this->delete('/api/activitylogs/' . $log->id);

        $this->assertDatabaseMissing('activity_log', ['id' => $log->id]);
    }

    public function test_does_archive_log_records_by_count()
    {
        ActivityLog::truncate();

        for ($i = 0; $i < 1000; $i++) {
            activity()->log($this->faker->sentence());
        }

        $firstlog = ActivityLog::all()->first();
        $lastlog = ActivityLog::all()->last();
        $this->assertDatabaseHas('activity_log', [
            'id' => $lastlog->id,
            'description' => $lastlog->description,
        ]);

        $logLast480 = ActivityLog::orderBy('id', 'desc')->take(480)->get();
        $this->assertCount(480, $logLast480); //+1 for system log added at archive job

        $output = Artisan::call('rconfig:archive-logs --rows=480');
        $result = Artisan::output();
        $arr = explode("\n", $result);
        $this->assertStringContainsString($arr[0], '480 logs entries sent to activity log archive table!');
        $this->assertCount(521, ActivityLog::all());

        $archiveLogEntry = ActivityLog::all()->last();
        $this->assertDatabaseHas('activity_log', [
            'id' => $archiveLogEntry->id,
            'description' => $archiveLogEntry->description,
        ]);

        $this->assertDatabaseHas('activity_log_archives', [
            'original_id' => $firstlog->id,
            'description' => $firstlog->description,
        ]);
        $this->assertDatabaseMissing('activity_log_archives', [
            'original_id' => $lastlog->id,
            'description' => $lastlog->description,
        ]);

        $this->assertCount(521, ActivityLog::all());
    }

    public function test_does_archive_log_records_by_age()
    {
        for ($i = 0; $i < 480; $i++) {
            activity()->log($this->faker->sentence());
        }

        DB::table('activity_log')->update(['created_at' => '2020-01-12 21:08:36', 'updated_at' => '2020-01-12 21:08:36']);
        $firstlog = ActivityLog::all()->first();

        $this->assertDatabaseHas('activity_log', [
            'id' => $firstlog->id,
            // 'created_at' => '2020-01-12 21:08:36',
        ]);

        for ($i = 0; $i < 500; $i++) {
            activity()->log($this->faker->sentence());
        }
        $lastlog = ActivityLog::all()->last();

        $this->assertDatabaseHas('activity_log', [
            'id' => $lastlog->id,
            // 'created_at' => now(),
        ]);

        $days = 10;

        $log500OlderThanToday = ActivityLog::where('created_at', '<=', now()->subDays($days)->toDateTimeString())->get();
        $this->assertGreaterThan(479, count($log500OlderThanToday));

        $output = Artisan::call('rconfig:archive-logs --days=10');
        $result = Artisan::output();
        $arr = explode("\n", $result);
        $this->assertStringContainsString($arr[0], 'logs older than 10 days sent to activity log archive table!');

        $this->assertGreaterThan(500, ActivityLog::count()); //+1 for the archive commands log entry
        $this->assertGreaterThan(479, count(ActivityLogArchive::all()));

        $this->assertDatabaseMissing('activity_log_archives', [
            'original_id' => $lastlog->id,
            'description' => $lastlog->description,
        ]);
        $this->assertDatabaseHas('activity_log_archives', [
            'original_id' => $firstlog->id,
            'description' => $firstlog->description,
        ]);
        ActivityLog::truncate();
    }

    public function test_load_more_mode_caps_per_page_at_50()
    {
        // Create 200 logs for testing pagination
        $logs = collect(range(1, 200))->map(fn ($i) => [
            'description' => "Test log entry {$i}",
            'log_name' => $this->faker->randomElement(['info', 'warn', 'error']),
            'subject_type' => null,
            'subject_id' => null,
            'causer_type' => null,
            'causer_id' => null,
            'properties' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('activity_log')->insert($logs->toArray());

        // Test 1: Without loadMore parameter and perPage=10000000, should return all records
        $response = $this->getJson('/api/activitylogs?page=1&perPage=10000000');
        $response->assertStatus(200);
        $this->assertEquals(200, count($response->json('data')), 'Without loadMore, should return all 200 records');

        // Test 2: With loadMore=true and perPage=10000000, should cap at 50 records per page
        $response = $this->getJson('/api/activitylogs?page=1&perPage=10000000&loadMore=true');
        $response->assertStatus(200);
        $this->assertEquals(50, count($response->json('data')), 'With loadMore=true, should cap at 50 records');
        $this->assertEquals(4, $response->json('last_page'), 'Should have 4 pages total (200/50)');
        $this->assertEquals(1, $response->json('current_page'), 'Should be on page 1');

        // Test 3: Verify loadMore parameter is appended to pagination links (Laravel converts true to 1)
        $nextPageUrl = $response->json('next_page_url');
        $this->assertTrue(
            str_contains($nextPageUrl, 'loadMore=true') || str_contains($nextPageUrl, 'loadMore=1'),
            'Next page URL should contain loadMore parameter'
        );

        // Test 4: Fetch page 2 with loadMore
        $response = $this->getJson('/api/activitylogs?page=2&perPage=10000000&loadMore=true');
        $response->assertStatus(200);
        $this->assertEquals(50, count($response->json('data')), 'Page 2 should also have 50 records');
        $this->assertEquals(2, $response->json('current_page'), 'Should be on page 2');

        // Test 5: Fetch page 4 (last page) with loadMore
        $response = $this->getJson('/api/activitylogs?page=4&perPage=10000000&loadMore=true');
        $response->assertStatus(200);
        $this->assertEquals(50, count($response->json('data')), 'Last page should have 50 records');
        $this->assertEquals(4, $response->json('current_page'), 'Should be on page 4');
        $this->assertNull($response->json('next_page_url'), 'Last page should have no next_page_url');

        // Test 6: Verify loadMore=false doesn't cap pagination
        $response = $this->getJson('/api/activitylogs?page=1&perPage=10000000&loadMore=false');
        $response->assertStatus(200);
        $this->assertEquals(200, count($response->json('data')), 'With loadMore=false, should return all records');

        // Test 7: Verify normal pagination (perPage=10) is not affected by loadMore
        $response = $this->getJson('/api/activitylogs?page=1&perPage=10&loadMore=true');
        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')), 'Normal perPage should not be affected by loadMore');
    }

    // tearDown
    public function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
