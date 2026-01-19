<?php

namespace App\CustomClasses;

use App\Models\Taskdownloadreport;
use Illuminate\Support\Facades\File;

class CreateTaskReport
{
    public $report_data;

    public function __construct($report_data)
    {
        $this->report_data = $report_data;
    }

    public function saveReport()
    {
        $this->report_data->duration = $this->report_data->end_time->diffInSeconds($this->report_data->start_time);

        File::put(
            $this->report_data->report_path,
            view('report_templates.task_report')
                ->with(['report_data' => $this->report_data])
                ->render()
        );

        $model = new Taskdownloadreport;
        $model->report_id = $this->report_data->report_id;
        $model->report_name = $this->report_data->report_name;
        $model->task_type = $this->report_data->task_type;
        $model->task_id = $this->report_data->task->id;
        $model->task_name = $this->report_data->task->task_name;
        $model->task_desc = $this->report_data->task->task_desc;
        $model->file_name = $this->report_data->file_name;
        $model->start_time = $this->report_data->start_time->toDateTimeString();
        $model->end_time = $this->report_data->end_time->toDateTimeString();
        $model->duration = $this->report_data->duration;
        $model->save();
    }
}
