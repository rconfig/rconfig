<?php

namespace App\Http\Controllers\Api;

use App\Models\TrackedJob;
use Illuminate\Http\Response;

class TrackedJobController extends ApiBaseController
{
    public function __construct(TrackedJob $model, $modelname = 'tracked_job')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $result = $this->model::where('device_id', $id)->orderBy('id', 'desc')->first();

        return $this->successResponse('Latest trackable job', $result);
    }
}
