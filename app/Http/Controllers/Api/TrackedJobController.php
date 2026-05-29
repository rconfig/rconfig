<?php

namespace App\Http\Controllers\Api;

use App\Models\TrackedJob;

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
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $result = $this->model::where('device_id', $id)->orderBy('id', 'desc')->first();

        return $this->successResponse('Latest trackable job', $result);
    }
}
