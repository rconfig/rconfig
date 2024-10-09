<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\FilterMultipleFields;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\MonitoredScheduledTasks;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\CronTranslator\CronTranslator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends ApiBaseController
{
    public function __construct(Task $model, $modelname = 'task')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['id', 'task_name'];

        $result = QueryBuilder::for(Task::class)
            ->with(['device'])
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'id', 'task_name'),
            ])
            ->defaultSort('-id')
            ->allowedSorts('id', 'task_name')
            ->paginate((int) $request->perPage);

        $result->map(function ($item) {
            $item['cron_plain'] = CronTranslator::translate(implode(' ', $item['task_cron']));
            return $item;
        });

        return response()->json($result);
    }

    public function store(StoreTaskRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->tag()->attach($request->tag);
        $model->device()->attach($request->device);
        $model->category()->attach($request->category);

        $this->SyncToMonitoredTasks($model);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

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

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);  // relationships are pulled from the model using 'protected $with' now
    }

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
