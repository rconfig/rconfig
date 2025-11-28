<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Jobs\TaskReportJob;
use App\Models\Config;
use App\Models\Taskdownloadreport;
use Carbon\Carbon;
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

        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user, 'api');
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
        $task = new stdClass();
        $task->id = 515151;
        $task->task_name = 'test_task';
        $task->task_desc = 'test_desc';
        $task->verbose_download_report_notify = 0;
        $report_data = collect();

        $report_data->report_id = (string) Str::uuid();
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

    protected function tearDown(): void
    {
        $this->rollBackTransaction();
        parent::tearDown();
    }
}
