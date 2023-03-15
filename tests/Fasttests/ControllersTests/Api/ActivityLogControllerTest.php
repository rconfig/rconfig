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
        $this->user = User::factory()->create();
        /** @var mixed $this->user */
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function get_all_logs()
    {
        for ($i = 0; $i < 100; $i++) {
            activity()->log($this->faker->sentence());
        }

        $response = $this->get('/api/activitylogs?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    /** @test */
    public function create_log_entry()
    {
        $log = activity()->log($this->faker->sentence());

        $this->assertDatabaseHas('activity_log', [
            'description' => $log->description,
        ]);
    }

    /** @test */
    public function show_single_log_entry()
    {
        $log = activity()->log($this->faker->sentence());

        $response = $this->get('/api/activitylogs/'.$log->id);

        $response->assertJsonFragment([
            'description' => $log->description,
        ]);
    }

    /** @test */
    public function test_logIt_helper_function()
    {
        // logIt($class, $function, $log_name, $description, $event_type, $device_name = null, $device_id = null, $connection_category = null, $connection_ids = null );
        $logMsg = $this->faker->sentence;
        $log = activityLogIt(__CLASS__, __FUNCTION__, 'info', $logMsg, 'downloader');
        $this->assertDatabaseHas('activity_log', [
            'description' => $logMsg,
        ]);
    }

    /** @test */
    public function get_last5_logs_by_deviceid()
    {
        $device_name = $this->faker->name;
        $device_id = $this->faker->randomDigit;
        for ($i = 0; $i < 100; $i++) {
            activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
        }

        $response = $this->get('/api/activitylogs/last5/'.$device_id);

        $response->assertSee($device_id);
        $response->assertJsonCount(5);
        $response->assertStatus(200);
        $this->assertDatabaseHas('activity_log', [
            'device_id' => $device_id,
        ]);
    }

    /** @test */
    public function get_log_stats_by_deviceid()
    {
        $device_name = $this->faker->name;
        $device_id = $this->faker->randomDigit;
        for ($i = 0; $i < 100; $i++) {
            activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
        }

        $response = $this->get('/api/activitylogs/device-stats/'.$device_id);
        $response->assertStatus(200);
        $response->assertSee('log_name');
    }

    /** @test */
    public function delete_log_entry()
    {
        $descr = $this->faker->sentence;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $descr, 'downloader');
        $this->assertDatabaseHas('activity_log', [
            'description' => $descr,
        ]);

        $log = ActivityLog::all()->last();
        $this->delete('/api/activitylogs/'.$log->id);

        $this->assertDatabaseMissing('activity_log', ['id' => $log->id]);
    }

    /** @test */
    public function does_archive_log_records_by_count()
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

    /** @test */
    public function does_archive_log_records_by_age()
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

        $this->assertCount(501, ActivityLog::all()); //+1 for the archive commands log entry
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

    // tearDown
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
