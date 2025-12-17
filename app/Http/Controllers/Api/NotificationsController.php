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
            ->where('notifiable_id', auth()->id())
            ->defaultSort('-created_at')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function update($id, Request $request)
    {
        // mark as read
        $model = Notification::find($id);
        $model->read_at = now();
        $model->save();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' updated successfully!');
    }

    public function markAllAsRead()
    {
        Notification::where('notifiable_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->successResponse('All notifications marked as read successfully!');
    }
}
