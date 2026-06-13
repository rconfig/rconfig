<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Jobs\TaskReportJob;
use App\Models\Config;
use App\Models\Taskdownloadreport;
use App\Models\User;
use Carbon\Carbon;
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

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
