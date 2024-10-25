<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\StoreDeviceCommentRequest;
use App\Http\Requests\UpdateDeviceCommentRequest;
use App\Models\DeviceComment;
use Illuminate\Http\Request;

class DeviceCommentController extends ApiBaseController
{
    public function __construct(DeviceComment $model, $modelname = 'device')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null) {}


    public function store(StoreDeviceCommentRequest $request)
    {
        //
    }

    public function show($deviceid, $relationship = null, $withCount = null)
    {
        $results = parent::show($deviceid, ['device', 'user']);

        return response()->json($results);
    }

    public function commentsByDeviceId($devieid)
    {
        sleep(1);
        $comments = DeviceComment::where('device_id', $devieid)->with('user')->get();
        return response()->json($comments);
    }


    public function edit(DeviceComment $deviceComment)
    {
        //
    }

    public function update(UpdateDeviceCommentRequest $request, DeviceComment $deviceComment)
    {
        //
    }

    public function destroy($id, $return = 0)
    {
        //
    }
}
