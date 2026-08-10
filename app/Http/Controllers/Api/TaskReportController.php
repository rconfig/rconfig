<?php

namespace App\Http\Controllers\Api;

use App\Models\Taskdownloadreport;
use App\Services\Utilities\PathContainmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskReportController extends ApiBaseController
{
    public function __construct(Taskdownloadreport $model, $modelname = 'taskdownloadreport')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['report_id', 'task_id', 'task_name', 'task_type'];

        try {
            $query = QueryBuilder::for($this->model::class)
                ->allowedFilters(...[
                    AllowedFilter::custom('q', new FilterMultipleFields, 'report_id, task_id, task_name, task_type'),
                ])
                ->defaultSort('-created_at')
                ->allowedSorts(...['id', 'report_name', 'task_id', 'created_at'])
                ->paginate($request->perPage ?? 10);
        } catch (\Exception $e) {
            return $this->failureResponse($e->getMessage());
        }

        return response()->json($query);
    }

    /**
     * Return either the rendered report HTML or the report record itself.
     *
     * A report id is a UUID and its file is named after it. When the id is not a UUID, or the
     * file is not there, the request falls through to the database so a lookup by numeric model
     * id keeps working. The guard therefore only ever chooses between those two paths; it must
     * not return a response of its own.
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $reportFile = $this->resolveReportFile((string) $id);

        if ($reportFile !== null) {
            return File::get($reportFile);
        }

        return parent::show($id, $relationship, $withCount);
    }

    /**
     * Resolve a report id to its HTML file inside the report directory.
     *
     * The UUID check is the containment guarantee rather than basename(): basename() would
     * quietly accept '../x' by rewriting it, whereas a UUID cannot contain a separator at all.
     *
     * @return string|null the resolved path, or null if this is not a report file we hold
     */
    private function resolveReportFile(string $id): ?string
    {
        if (! Str::isUuid($id)) {
            return null;
        }

        return (new PathContainmentService)->resolveFileWithin(report_path(), report_path() . $id . '.html');
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        if (File::exists($model->config_location)) {
            File::delete($model->config_location);
            $logmsg = 'Config File : ' . $model->config_location . ' was deleted';
        } else {
            $logmsg = 'Unable to find file from path: ' . $model->config_location;
            $this->failureResponse($logmsg);
        }

        activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'config', '', $model->device_id, 'device');

        return $this->successResponse($logmsg);
    }
}
