<?php

use App\Models\ActivityLog;
use App\Models\ActivityLogArchive;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(WithFaker::class);

beforeEach(function () {
    $this->beginTransaction();

    ActivityLog::truncate();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all logs', function () {
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
});

test('get all logs with device id filter', function () {
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

    expect(ActivityLog::count())->toEqual(60);

    // Fixed URL syntax
    $response = $this->get('/api/activitylogs?page=1&perPage=100&filter[device_id]=1001');

    expect(count($response['data']))->toEqual(20);
    // Should be 20, not 60
    $response->assertStatus(200);
});

test('create log entry', function () {
    $log = activity()->log($this->faker->sentence());

    $this->assertDatabaseHas('activity_log', [
        'description' => $log->description,
    ]);
});

test('show single log entry', function () {
    $log = activity()->log($this->faker->sentence());

    $response = $this->get('/api/activitylogs/' . $log->id);

    $response->assertJsonFragment([
        'description' => $log->description,
    ]);
});

test('test log it helper function', function () {
    // logIt($class, $function, $log_name, $description, $event_type, $device_name = null, $device_id = null, $connection_category = null, $connection_ids = null );
    $logMsg = $this->faker->sentence;
    $log = activityLogIt(__CLASS__, __FUNCTION__, 'info', $logMsg, 'downloader');
    $this->assertDatabaseHas('activity_log', [
        'description' => $logMsg,
    ]);
});

test('get last5 logs by deviceid', function () {
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
});

test('get log stats by deviceid', function () {
    $device_name = $this->faker->name;
    $device_id = $this->faker->randomDigit;
    for ($i = 0; $i < 100; $i++) {
        activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
    }

    $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
    $response->assertStatus(200);
    $response->assertSee('log_name');
});

test('clear logs by deviceid', function () {
    $device_name = $this->faker->name;
    $device_id = $this->faker->randomDigit;
    for ($i = 0; $i < 100; $i++) {
        activityLogIt(__CLASS__, __FUNCTION__, $this->faker->randomElement(['error', 'warn', 'info']), $this->faker->sentence, 'downloader', $device_name, $device_id);
    }

    $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
    $response->assertStatus(200);
    expect($response->json())->toHaveCount(3);

    $response = $this->get('/api/activitylogs/clear-logs/' . $device_id);
    $response->assertStatus(200);

    $response = $this->get('/api/activitylogs/device-stats/' . $device_id);
    $response->assertStatus(200);
    expect($response->json())->toHaveCount(0);
});

test('delete log entry', function () {
    $descr = $this->faker->sentence;
    activityLogIt(__CLASS__, __FUNCTION__, 'info', $descr, 'downloader');
    $this->assertDatabaseHas('activity_log', [
        'description' => $descr,
    ]);

    $log = ActivityLog::latest()->first();
    $this->delete('/api/activitylogs/' . $log->id);

    $this->assertDatabaseMissing('activity_log', ['id' => $log->id]);
});

test('does archive log records by count', function () {
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
    expect($logLast480)->toHaveCount(480);

    // +1 for system log added at archive job
    $output = Artisan::call('rconfig:archive-logs --rows=480');
    $result = Artisan::output();
    $arr = explode("\n", $result);
    $this->assertStringContainsString($arr[0], '480 logs entries sent to activity log archive table!');
    expect(ActivityLog::all())->toHaveCount(521);

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

    expect(ActivityLog::all())->toHaveCount(521);
});

test('does archive log records by age', function () {
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
    expect(count($log500OlderThanToday))->toBeGreaterThan(479);

    $output = Artisan::call('rconfig:archive-logs --days=10');
    $result = Artisan::output();
    $arr = explode("\n", $result);
    $this->assertStringContainsString($arr[0], 'logs older than 10 days sent to activity log archive table!');

    expect(ActivityLog::count())->toBeGreaterThan(500);
    // +1 for the archive commands log entry
    expect(count(ActivityLogArchive::all()))->toBeGreaterThan(479);

    $this->assertDatabaseMissing('activity_log_archives', [
        'original_id' => $lastlog->id,
        'description' => $lastlog->description,
    ]);
    $this->assertDatabaseHas('activity_log_archives', [
        'original_id' => $firstlog->id,
        'description' => $firstlog->description,
    ]);
    ActivityLog::truncate();
});

test('load more mode caps per page at 50', function () {
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
    expect(count($response->json('data')))->toEqual(200, 'Without loadMore, should return all 200 records');

    // Test 2: With loadMore=true and perPage=10000000, should cap at 50 records per page
    $response = $this->getJson('/api/activitylogs?page=1&perPage=10000000&loadMore=true');
    $response->assertStatus(200);
    expect(count($response->json('data')))->toEqual(50, 'With loadMore=true, should cap at 50 records');
    expect($response->json('last_page'))->toEqual(4, 'Should have 4 pages total (200/50)');
    expect($response->json('current_page'))->toEqual(1, 'Should be on page 1');

    // Test 3: Verify loadMore parameter is appended to pagination links (Laravel converts true to 1)
    $nextPageUrl = $response->json('next_page_url');
    expect(str_contains($nextPageUrl, 'loadMore=true') || str_contains($nextPageUrl, 'loadMore=1'))->toBeTrue('Next page URL should contain loadMore parameter');

    // Test 4: Fetch page 2 with loadMore
    $response = $this->getJson('/api/activitylogs?page=2&perPage=10000000&loadMore=true');
    $response->assertStatus(200);
    expect(count($response->json('data')))->toEqual(50, 'Page 2 should also have 50 records');
    expect($response->json('current_page'))->toEqual(2, 'Should be on page 2');

    // Test 5: Fetch page 4 (last page) with loadMore
    $response = $this->getJson('/api/activitylogs?page=4&perPage=10000000&loadMore=true');
    $response->assertStatus(200);
    expect(count($response->json('data')))->toEqual(50, 'Last page should have 50 records');
    expect($response->json('current_page'))->toEqual(4, 'Should be on page 4');
    expect($response->json('next_page_url'))->toBeNull('Last page should have no next_page_url');

    // Test 6: Verify loadMore=false doesn't cap pagination
    $response = $this->getJson('/api/activitylogs?page=1&perPage=10000000&loadMore=false');
    $response->assertStatus(200);
    expect(count($response->json('data')))->toEqual(200, 'With loadMore=false, should return all records');

    // Test 7: Verify normal pagination (perPage=10) is not affected by loadMore
    $response = $this->getJson('/api/activitylogs?page=1&perPage=10&loadMore=true');
    $response->assertStatus(200);
    expect(count($response->json('data')))->toEqual(10, 'Normal perPage should not be affected by loadMore');
});

// tearDown
afterEach(function () {
    $this->rollBackTransaction();
});
