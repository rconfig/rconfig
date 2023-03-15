<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\MonitoredScheduledTasks;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\CronTranslator\CronTranslator;

class TaskController extends ApiBaseController
{
    public function __construct(Task $model, $modelname = 'task')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['id', 'task_name'];
        $result = parent::index($request, $searchCols); // relationships are pulled from the model using 'protected $with' now

        $result->map(function ($item) {
            $item['cron_plain'] = CronTranslator::translate(implode(' ', $item['task_cron']));

            return $item;
        });

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTaskRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->tag()->attach($request->tag);
        $model->device()->attach($request->device);
        $model->category()->attach($request->category);

        $this->SyncToMonitoredTasks($model);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

    /**
     * Validate a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateTask(StoreTaskRequest $request)
    {
        $model = $request->toDTO()->toArray();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' validated successfully!');
    }

    // get failed tasks
    public function failedJobsLast24HrsCount()
    {
        $res = DB::table('failed_jobs')->where('failed_at', '>=', Carbon::now()->subDays(1))->count();

        return $this->successResponse('Success', $res);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);  // relationships are pulled from the model using 'protected $with' now
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $user
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateTaskRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);
        $model->id = $id;

        $model->tag()->sync($request->tag);
        $model->device()->sync($request->device);
        $model->category()->sync($request->category);

        $this->SyncToMonitoredTasks($model);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!', ['id' => $model->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->delete();
        $model->tag()->detach();
        $model->category()->detach();
        $model->tag()->detach();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    private function SyncToMonitoredTasks($model)
    {
        MonitoredScheduledTasks::updateOrCreate(
            ['task_id' => $model->id],
            [
                'task_id' => $model->id,
                'name' => $model->task_name,
                'cron_expression' => implode(' ', $model->task_cron),
                'type' => $model->task_command,
            ]
        );
    }
}
