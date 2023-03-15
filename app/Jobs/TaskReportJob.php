<?php

namespace App\Jobs;

use App\CustomClasses\CreateTaskReport;
use App\Models\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TaskReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function handle()
    {
        $this->report_data->config_data_success = Config::select('device_id', 'device_name', 'device_category', 'type', 'download_status', 'report_id', 'start_time', 'end_time', 'duration', DB::raw('count(*) as command_count'))
            ->where('report_id', $this->report_data->report_id)
            ->where('download_status', 1)
            ->groupBy('device_name')
            ->get();

        $this->report_data->config_data_failed = Config::select('device_id', 'device_name', 'device_category', 'type', 'download_status', 'report_id', 'start_time', 'end_time', 'duration', DB::raw('count(*) as command_count'))
            ->where('report_id', $this->report_data->report_id)
            ->where('download_status', 0)
            ->groupBy('device_name')
            ->get();

        (new CreateTaskReport($this->report_data))->saveReport();

        $logmsg = 'Running CreateTaskReport job for ' . $this->report_data->task->id;
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'reports');
    }
}
