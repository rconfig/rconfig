<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackedJob;
use App\Traits\RespondsWithHttpStatus;

class TrackedJobController extends Controller
{
    use RespondsWithHttpStatus;

    public function __construct(TrackedJob $model, $modelname = 'tracked_job')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = $this->model::where('device_id', $id)->orderBy('id', 'desc')->first();

        return $this->successResponse('Latest trackable job', $result);
    }
}
