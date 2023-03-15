<?php

namespace App\Http\Controllers\Api;

use App\Models\Taskdownloadreport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TaskReportController extends ApiBaseController
{
    public function __construct(Taskdownloadreport $model, $modelname = 'taskdownloadreport')
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
        $searchCols = ['report_id', 'task_id', 'task_name', 'task_type'];

        return response()->json(parent::index($request, $searchCols));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Command  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $reportfile = report_path().$id.'.html';

        if (File::exists($reportfile)) {
            return File::get($reportfile);
        } else {
            $this->failureResponse('File not found');
        }

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
        $model = parent::destroy($id, 1);

        if (File::exists($model->config_location)) {
            File::delete($model->config_location);
            $logmsg = 'Config File : '.$model->config_location.' was deleted';
        } else {
            $logmsg = 'Unable to find file from path: '.$model->config_location;
            $this->failureResponse($logmsg);
        }

        activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'config', '', $model->device_id, 'device');

        return $this->successResponse($logmsg);
    }

    public function getReport(Request $request)
    {
        $reportfile = report_path().$request->id.'.html';

        if (File::exists($reportfile)) {
            return File::get($reportfile);
        } else {
            return response()->json([
                'status' => 'error',
                'msg' => 'Error',
                'errors' => 'File not found!',
            ], 422);
        }
    }
}
