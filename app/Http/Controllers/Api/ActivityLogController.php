<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityLogController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(ActivityLog $model, $modelname = 'ActivityLog')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        // Load More mode detection
        $isLoadMoreMode = $request->has('loadMore') && $request->boolean('loadMore');
        $perPage = (int) $request->perPage;

        // If load more mode and user selected "All", cap at 50 per page
        if ($isLoadMoreMode && $perPage >= 10000000) {
            $perPage = 50;
        }

        $response = QueryBuilder::for(ActivityLog::class)
            ->allowedFilters(['description', 'log_name', 'device_id', 'event_type'])
            ->defaultSort('-id')
            ->allowedSorts('id', 'log_name')
            ->paginate($perPage);

        // Append loadMore parameter to pagination links
        if ($isLoadMoreMode) {
            $response->appends(['loadMore' => true]);
        }
        return response()->json($response);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function getLast5($id)
    {
        return ActivityLog::where('device_id', $id)->latest()->take(5)->get();
    }

    public function showStatsByDeviceId($id)
    {
        return DB::table('activity_log')
            ->select('log_name', DB::raw('count(*) as total'))
            ->where('device_id', $id)
            ->groupBy('log_name')
            ->get();
    }

    public function clearLogsByDeviceId($id)
    {

        DB::table('activity_log')
            ->where('device_id', $id)
            ->delete();

        return $this->successResponse('Logs cleared successfully!');
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }
}
