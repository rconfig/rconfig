<?php

namespace App\Console\Commands;

use App\CustomClasses\DeviceDownloadClass;
use App\CustomClasses\FilterArgsForCommnds;
use App\CustomClasses\GetAndCheckCategoryIds;
use App\CustomClasses\GetAndCheckDeviceIds;
use App\CustomClasses\GetAndCheckTagIds;
use App\CustomClasses\GetAndCheckTaskIds;
use App\Jobs\DeviceDownloadJob;
use App\Jobs\SendTaskReportNotificationJob;
use App\Jobs\TaskCompleteNotificationJob;
use App\Jobs\TaskReportJob;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class rconfigTaskDownload extends Command
{
    protected $signature = 'rconfig:download-task
                            {taskid*}
                            {--d|debug : rConfig debugging output}';

    protected $eventtype = 'rconfig:download-task';

    protected $description = 'Download configurations for devices with one or multiple tasks';

    protected $report_data;

    protected $devicerecords;

    protected $debug;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->error('This operation can take some time, depending on how many devices are configured for this task!!!');

        // get ids from command
        $ids = (new FilterArgsForCommnds)->filterArgs($this->argument('taskid'));
        $this->debug = $this->option('debug');

        $this->info('Start ' . $this->eventtype . ' IDs:' . implode(' ', $ids));

        $logmsg = 'CLI Download Started for ' . ($this->eventtype . 'IDs:' . implode(' ', $ids));
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

        $tasks = (new GetAndCheckTaskIds($ids))->GetTaskRecords();

        // error if none returned
        if (count($tasks) === 0) {
            $this->info('No task records returned. Download will now terminate!');

            return;
        }
        $this->report_data = collect();
        foreach ($tasks as $task) {

            $this->report_data->report_id = (string) Str::uuid();
            $this->report_data->task_type = 'Task Download Report';
            $this->report_data->task = $task;
            $this->report_data->start_time = Carbon::now();

            //CATEGORY BELOW HERE
            if ($task->category->count() > 0) {
                $categoryrecords = (new GetAndCheckCategoryIds($task->category->pluck('id')->toArray()))->GetCategoryRecords();

                if (!$this->checkRecordCount(count($categoryrecords))) {
                    return;
                }

                foreach ($categoryrecords as $categoryrecord) {
                    $this->devicerecords = $categoryrecord->device;
                    if ($this->debug) {
                        $this->runDeviceManualJobs();
                    } else {
                        $this->runDeviceBatchJob($task->task_name, 'Category: ' . $categoryrecord->categoryName . ' [' . $categoryrecord->id . ']');
                    }
                }
            }

            //TAGS BELOW HERE
            if ($task->tag->count() > 0) {
                $tagrecords = (new GetAndCheckTagIds($task->tag->pluck('id')->toArray()))->GetTagRecords();
                if (!$this->checkRecordCount(count($tagrecords))) {
                    return;
                }

                foreach ($tagrecords as $tagrecord) {
                    $this->devicerecords = $tagrecord->device;
                    if ($this->debug) {
                        $this->runDeviceManualJobs();
                    } else {
                        $this->runDeviceBatchJob($task->task_name, 'Tag: ' . $tagrecord->tagname . ' [' . $tagrecord->id . ']');
                    }
                }
            }

            //DEVICES BELOW HERE
            if ($task->device->count() > 0) {
                $this->devicerecords = (new GetAndCheckDeviceIds($task->device->pluck('id')->toArray()))->GetDeviceRecords();
                if (!$this->checkRecordCount(count($this->devicerecords))) {
                    return;
                }

                if ($this->debug) {
                    $this->runDeviceManualJobs();
                } else {
                    $this->runDeviceBatchJob($task->task_name, 'Devices [Count: ' . $task->device->count() . ']');
                }
            }

            if (!isDocker()) {
                if (app()->runningInConsole()) {
                    custom_chown(storage_path());
                }
            }

            $this->create_report();

            $logmsg = 'CLI Download ended for ' . ($this->eventtype . ' IDs:' . implode(' ', $ids));
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype, $ids);

            $this->info('');
            $this->info('End ' . $this->eventtype . '');
        }
    }

    private function runDeviceManualJobs()
    {
        $output = (new DeviceDownloadClass($this->devicerecords, $this->eventtype, $this->debug, $this->report_data->report_id))->downloadDevices();
        foreach ($output as $output_type => $values) {
            foreach ($values as $key1 => $value1) {
                $this->$output_type($value1); // cli output type and value
            }
        }
    }

    private function runDeviceBatchJob($taskname, $jobname = '')
    {

        $jobs = [];
        foreach ($this->devicerecords as $devicerecord) {

            $jobs[] = new DeviceDownloadJob($devicerecord, $this->eventtype, $this->debug, $this->report_data->report_id);
        }

        $batch = Bus::batch($jobs)->then(function (Batch $batch) {
            // All jobs completed successfully...
            $msg = 'Batch [' . $batch->id . ']/[' . $batch->name . '] has been finished successfully';
            info($msg);
            activity('info')->log($msg);
        })->catch(function (Batch $batch, \Throwable $e) {
            // First batch job failure detected...
            $msg = 'Batch [' . $batch->id . ']/[' . $batch->name . '] failed to process all jobs';
            info($msg);
            activity('error')->log($msg);
            activity('error')->log($e->getMessage());
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
            $msg = 'Batch [' . $batch->id . ']/[' . $batch->name . '] finished processing';
            info($msg);
            activity('info')->log($msg);
            DB::table('job_batches')->where('id', $batch->id)->update(['finished_at' => now()->timestamp]); // need this as allowFailures( does not write finished_at)
        })->name('Process TestBatchJob')
            ->allowFailures()
            ->onQueue('downloadqueue')
            ->name('download-task: ' . $taskname . ' (' . $jobname . ')')
            ->dispatch();

        $progress = $this->output->createProgressBar(100);
        $this->info('Total device download jobs: ' . $batch->totalJobs);
        $this->info('');

        if (!\App::runningUnitTests()) {
            while (($batch = $batch->fresh()) && !$batch->finished()) {
                // dump('Pending: ' . $batch->pendingJobs);
                // dump('Status: ' . $batch->finished());
                $progress->setProgress($batch->progress());
                sleep(3);
            }
        }

        $progress->finish();
        $this->info('');
    }

    private function create_report()
    {

        activity('info')->log('Creating report for ' . $this->report_data->task->task_name);
        $this->report_data->end_time = Carbon::now();
        $this->report_data->file_name = $this->report_data->report_id . '.html';
        $this->report_data->report_path = report_path() . $this->report_data->file_name;

        if (App::runningUnitTests()) { // required for testing
            dispatch(new TaskReportJob($this->report_data))->onConnection('sync');
        } else {
            dispatch(new TaskReportJob($this->report_data));
        }

        if ($this->report_data->task->task_email_notify == 1) {
            dispatch(new TaskCompleteNotificationJob($this->report_data));
        }

        if ($this->report_data->task->download_report_notify == 1) {
            \Log::info('Sending report notification: SendTaskReportNotificationJob');
            dispatch(new SendTaskReportNotificationJob($this->report_data));
        }

        activity('info')->log('Report completed for ' . $this->report_data->task->task_name);
    }

    private function checkRecordCount($count)
    {
        $status = true;
        if ($count === 0) {
            $logmsg = 'No category/ task or tag records returned. Download will now terminate!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'connection', '', '', $this->eventtype);
            $this->info($logmsg);
            $status = false;
        }

        return $status;
    }
}
