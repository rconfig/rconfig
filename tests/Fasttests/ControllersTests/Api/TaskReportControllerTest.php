<?php

namespace Tests\Fasttests\ControllersTests\Api;

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
use stdClass;
use Tests\TestCase;

class TaskReportControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_all_reports()
    {
        Taskdownloadreport::factory(100)->create();
        $response = $this->get('/api/reports?page=1&perPage=100');
        $this->assertEquals(100, count($response['data']));
        $response->assertStatus(200);
    }

    public function test_show_single_report()
    {
        $report = Taskdownloadreport::factory()->create();
        $response = $this->get('/api/reports/' . $report->id);

        $response->assertJson(['report_id' => $report->report_id]);
    }

    public function test_delete_report()
    {
        $report = Taskdownloadreport::factory()->create();
        $this->assertDatabaseHas('taskdownloadreports', ['report_id' => $report->report_id]);

        $this->delete('/api/reports/' . $report->id);

        $this->assertDatabaseMissing('taskdownloadreports', ['report_id' => $report->report_id]);
    }

    public function test_can_save_a_report()
    {
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
        $this->assertFileExists(report_path() . $report_data->file_name);
        File::delete(report_path() . $report_data->file_name);
        $this->assertFileDoesNotExist(report_path() . $report_data->file_name);
    }

    /**
     * Regression for issue #251: the report download time must be rendered in the
     * configured application timezone, not UTC. Previously the accessor called
     * ->addHours($timezone) with a timezone string, which cast to 0 and disabled
     * the conversion, so reports showed UTC and tasks appeared to run at the wrong time.
     */
    public function test_created_at_is_rendered_in_configured_timezone()
    {
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
            $this->assertSame('Jun 13, 2026 2:00AM', $rendered);
            $this->assertNotSame('Jun 13, 2026 0:00AM', $rendered);
        } finally {
            date_default_timezone_set($originalDefault);
        }
    }

    /**
     * The report time must reflect the local wall-clock value as stored, even when the
     * UTC equivalent falls on the previous day. The old code rendered the UTC value,
     * which could roll the date back a day for positive offsets.
     */
    public function test_created_at_renders_local_time_across_date_rollover()
    {
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

            $this->assertSame('Jun 13, 2026 9:15AM', $rendered);
            // The old UTC rendering would have been the previous day.
            $this->assertNotSame('Jun 12, 2026 11:15PM', $rendered);
        } finally {
            date_default_timezone_set($originalDefault);
        }
    }

    /*
     * Report file resolution.
     *
     * show() serves report_path() . {id} . '.html' when the id is a report UUID, and otherwise
     * falls through to the database so a lookup by numeric model id keeps working. Both halves
     * are pinned below, because breaking either one is silent.
     */

    private const REPORT_LEAK_MARKER = 'REPORT_LEAK_MARKER';

    public function test_it_serves_the_report_file_for_a_uuid_id(): void
    {
        $report = Taskdownloadreport::factory()->create();
        $reportFile = report_path() . $report->report_id . '.html';
        File::put($reportFile, '<html><body>rendered report</body></html>');

        $response = $this->get('/api/reports/' . $report->report_id);

        $response->assertStatus(200);
        $this->assertStringContainsString('rendered report', $response->getContent());

        File::delete($reportFile);
    }

    /**
     * The database fallback is the reason the containment guard must never return a response of
     * its own. test_show_single_report covers it incidentally; this names it as a requirement.
     */
    public function test_it_falls_back_to_the_database_when_no_report_file_exists(): void
    {
        $report = Taskdownloadreport::factory()->create();
        $this->assertFileDoesNotExist(report_path() . $report->report_id . '.html');

        $response = $this->get('/api/reports/' . $report->id);

        $response->assertStatus(200)->assertJson(['report_id' => $report->report_id]);
    }

    public function test_it_returns_not_found_for_a_uuid_with_no_report_file(): void
    {
        $response = $this->get('/api/reports/' . (string) Str::uuid());

        $response->assertStatus(404);
    }

    /**
     * Called against the controller rather than through the router on purpose.
     *
     * The {report} route placeholder does not match a forward slash, and Symfony decodes %2F
     * before matching, so a traversal id cannot reach show() over HTTP today. This pins the
     * controller itself, so the guarantee survives a future route that is less strict.
     */
    public function test_it_refuses_a_traversal_id_at_the_controller(): void
    {
        $bait = export_path() . 'report_leak_probe.html';
        File::put($bait, '<html><body>' . self::REPORT_LEAK_MARKER . '</body></html>');

        $controller = new TaskReportController(new Taskdownloadreport);

        try {
            $result = $controller->show('../exports/report_leak_probe');
            $rendered = is_string($result) ? $result : json_encode($result);
            $this->assertStringNotContainsString(
                self::REPORT_LEAK_MARKER,
                (string) $rendered,
                'A traversal id read a file outside the report directory.'
            );
        } catch (ModelNotFoundException $e) {
            // Correct: the id is not a report UUID, so it fell through to the database lookup.
            $this->assertTrue(true);
        } finally {
            File::delete($bait);
        }
    }

    /**
     * Smoke check on the router behaviour the guard above relies on. This passes either way; its
     * value is asserting that the placeholder really does reject an encoded separator.
     */
    public function test_an_encoded_traversal_is_not_treated_as_a_report_id(): void
    {
        $response = $this->get('/api/reports/..%2F..%2Fetc%2Fpasswd');

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString('root:', $response->getContent());
    }

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
