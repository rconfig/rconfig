<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityLogController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(ActivityLog $model, $modelname = 'ActivityLog')
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
        $searchCols = ['description', 'log_name'];

        return response()->json(parent::index($request, $searchCols));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityLog  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityLog  $tag
     * @return \Illuminate\Http\Response
     */
    public function getLast5($id)
    {
        return ActivityLog::where('device_id', $id)->latest()->take(5)->get();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityLog  $tag
     * @return \Illuminate\Http\Response
     */
    public function showStatsByDeviceId($id)
    {
        return DB::table('activity_log')
            ->select('log_name', DB::raw('count(*) as total'))
            ->where('device_id', $id)
            ->groupBy('log_name')
            ->get();
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

        return $this->successResponse(Str::ucfirst($this->modelname).' deleted successfully!');
    }
}
