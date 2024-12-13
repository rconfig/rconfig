<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\StoreDeviceCommentRequest;
use App\Http\Requests\UpdateDeviceCommentRequest;
use App\Models\DeviceComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceCommentController extends ApiBaseController
{
    public function __construct(DeviceComment $model, $modelname = 'device')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null) {}


    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = auth()->user()->id;
        $comment = DeviceComment::create($data);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!', ['id' => $comment->id]);
    }

    public function show($deviceid, $relationship = null, $withCount = null)
    {
        $results = parent::show($deviceid, ['device', 'user']);

        return response()->json($results);
    }

    public function activeCommentsByDeviceId($devieid)
    {
        $comments = DeviceComment::where('device_id', $devieid)->where('is_open', 1)->with('user')->get();
        return response()->json($comments);
    }

    public function closedCommentsByDeviceId($devieid)
    {
        $comments = DeviceComment::where('device_id', $devieid)->where('is_open', 0)->with('user')->get();
        return response()->json($comments);
    }

    public function resolve($id)
    {
        $comment = DeviceComment::find($id);
        $comment->is_open = 0;
        $comment->save();

        return $this->successResponse('Comment resolved successfully!');
    }


    public function edit(DeviceComment $deviceComment)
    {
        //
    }

    public function update(Request $request, DeviceComment $deviceComment)
    {
        //
    }

    public function destroy($id, $return = 0)
    {
        //
    }
}
