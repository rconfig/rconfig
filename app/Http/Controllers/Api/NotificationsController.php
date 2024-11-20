<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommandRequest;
use App\Models\Command;
use App\Models\Notification;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationsController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(Notification $model, $modelname = 'notification')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $response = QueryBuilder::for(Notification::class)
            ->whereNull('read_at')
            ->defaultSort('-created_at')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    // public function store(StoreCommandRequest $request)
    // {
    //     $model = parent::storeResource($request->toDTO()->toArray(), 1);

    //     $model->Category()->attach(collect($request->category)->pluck('id'));

    //     return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    // }

    // public function show($id, $relationship = null, $withCount = null)
    // {
    //     return parent::show($id, ['category']);
    // }

    public function update($id, Request $request)
    {
        // mark as read
        $model = Notification::find($id);
        $model->read_at = now();
        $model->save();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' updated successfully!');
    }

    // public function destroy($id, $return = 0)
    // {
    //     $model = parent::destroy($id, 1);

    //     $model->Category()->detach();

    //     return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    // }

    // public function deleteMany(Request $request)
    // {
    //     $ids = $request->input('ids');
    //     Command::whereIn('id', $ids)->delete();

    //     return response()->json(['message' => 'Commands deleted successfully'], 200);
    // }
}
