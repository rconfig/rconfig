<?php

use App\Http\Controllers\Api\TaskReportController;
use App\Jobs\TaskReportJob;
use App\Models\Config;
use App\Models\Taskdownloadreport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->beginTransaction();

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('get all reports', function () {
    Taskdownloadreport::factory(100)->create();
    $response = $this->get('/api/reports?page=1&perPage=100');
    expect(count($response['data']))->toEqual(100);
    $response->assertStatus(200);
});

test('show single report', function () {
    $report = Taskdownloadreport::factory()->create();
    $response = $this->get('/api/reports/' . $report->id);

    $response->assertJson(['report_id' => $report->report_id]);
});

test('delete report', function () {
    $report = Taskdownloadreport::factory()->create();
    $this->assertDatabaseHas('taskdownloadreports', ['report_id' => $report->report_id]);

    $this->delete('/api/reports/' . $report->id);

    $this->assertDatabaseMissing('taskdownloadreports', ['report_id' => $report->report_id]);
});

test('can save a report', function () {
    $task = new stdClass;
    $task->id = 515151;
    $task->task_name = 'test_task';
    $task->task_desc = 'test_desc';
    $task->verbose_download_report_notify = 0;
    $report_data = collect();

    $report_data->report_id = (string) Str::uuid();
    $report_data->report_name = "device_download_report_{$task->id}";
    $report_data->task_type = 'Task Download Report';
    $report_data->task = $task;
    $report_data->start_time = Carbon::now();
    $report_data->end_time = Carbon::now()->addSeconds(120);
    $report_data->file_name = $report_data->report_id . '.html';
    $report_data->report_path = report_path() . $report_data->file_name;

    Config::factory(100)->create(['report_id' => $report_data->report_id]);
    dispatch(new TaskReportJob($report_data))->onConnection('sync');

    $this->assertDatabaseHas('taskdownloadreports', ['file_name' => $report_data->file_name]);
    expect(report_path() . $report_data->file_name)->toBeFile();
    File::delete(report_path() . $report_data->file_name);
    $this->assertFileDoesNotExist(report_path() . $report_data->file_name);
});

test('created at is rendered in configured timezone', function () {
    $originalDefault = date_default_timezone_get();

    try {
        // Simulate a user whose configured timezone has a non-zero UTC offset.
        ConfigFacade::set('app.timezone', 'Europe/Rome');
        date_default_timezone_set('Europe/Rome');

        $report = Taskdownloadreport::factory()->create();

        // Force a known local wall-clock time (02:00 Rome local on a CEST date, UTC+2).
        DB::table('taskdownloadreports')
            ->where('id', $report->id)
            ->update(['created_at' => '2026-06-13 02:00:00']);

        $rendered = Taskdownloadreport::find($report->id)->created_at;

        // It must show the local time the task ran, not the UTC equivalent.
        expect($rendered)->toBe('Jun 13, 2026 2:00AM');
        $this->assertNotSame('Jun 13, 2026 0:00AM', $rendered);
    } finally {
        date_default_timezone_set($originalDefault);
    }
});

test('created at renders local time across date rollover', function () {
    $originalDefault = date_default_timezone_get();

    try {
        // Sydney is UTC+10 in June, so 09:15 local is 23:15 UTC on the previous day.
        ConfigFacade::set('app.timezone', 'Australia/Sydney');
        date_default_timezone_set('Australia/Sydney');

        $report = Taskdownloadreport::factory()->create();
        DB::table('taskdownloadreports')
            ->where('id', $report->id)
            ->update(['created_at' => '2026-06-13 09:15:00']);

        $rendered = Taskdownloadreport::find($report->id)->created_at;

        expect($rendered)->toBe('Jun 13, 2026 9:15AM');
        // The old UTC rendering would have been the previous day.
        $this->assertNotSame('Jun 12, 2026 11:15PM', $rendered);
    } finally {
        date_default_timezone_set($originalDefault);
    }
});

/*
 * Report file resolution.
 *
 * show() serves report_path() . {id} . '.html' when the id is a report UUID, and otherwise
 * falls through to the database so a lookup by numeric model id keeps working. Both halves
 * are pinned below, because breaking either one is silent.
 */
const REPORT_LEAK_MARKER = 'REPORT_LEAK_MARKER';

test('it serves the report file for a uuid id', function () {
    $report = Taskdownloadreport::factory()->create();
    $reportFile = report_path() . $report->report_id . '.html';
    File::put($reportFile, '<html><body>rendered report</body></html>');

    $response = $this->get('/api/reports/' . $report->report_id);

    $response->assertStatus(200);
    $this->assertStringContainsString('rendered report', $response->getContent());

    File::delete($reportFile);
});

test('it falls back to the database when no report file exists', function () {
    $report = Taskdownloadreport::factory()->create();
    $this->assertFileDoesNotExist(report_path() . $report->report_id . '.html');

    $response = $this->get('/api/reports/' . $report->id);

    $response->assertStatus(200)->assertJson(['report_id' => $report->report_id]);
});

test('it returns not found for a uuid with no report file', function () {
    $response = $this->get('/api/reports/' . (string) Str::uuid());

    $response->assertStatus(404);
});

test('it refuses a traversal id at the controller', function () {
    $bait = export_path() . 'report_leak_probe.html';
    File::put($bait, '<html><body>' . REPORT_LEAK_MARKER . '</body></html>');

    $controller = new TaskReportController(new Taskdownloadreport);

    try {
        $result = $controller->show('../exports/report_leak_probe');
        $rendered = is_string($result) ? $result : json_encode($result);
        $this->assertStringNotContainsString(
            REPORT_LEAK_MARKER,
            (string) $rendered,
            'A traversal id read a file outside the report directory.'
        );
    } catch (ModelNotFoundException $e) {
        // Correct: the id is not a report UUID, so it fell through to the database lookup.
        expect(true)->toBeTrue();
    } finally {
        File::delete($bait);
    }
});

test('an encoded traversal is not treated as a report id', function () {
    $response = $this->get('/api/reports/..%2F..%2Fetc%2Fpasswd');

    $this->assertNotSame(200, $response->getStatusCode());
    $this->assertStringNotContainsString('root:', $response->getContent());
});

afterEach(function () {
    $this->rollBackTransaction();
});
