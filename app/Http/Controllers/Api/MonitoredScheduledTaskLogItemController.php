<?php

namespace App\Http\Controllers\Api;

use App\Models\MonitoredScheduledTaskLogItems;
use Illuminate\Http\Request;

class MonitoredScheduledTaskLogItemController extends ApiBaseController
{
    public function __construct(MonitoredScheduledTaskLogItems $model, $modelname = 'MonitoredScheduledTaskLogItems')
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
        return response()->json(parent::index($request, $searchCols));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MonitoredScheduledTaskLogItems  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return $this->model::where('task_id', $id)->orderBy('id', 'desc')->paginate();

        return parent::show($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }
}
