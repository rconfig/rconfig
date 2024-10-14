<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommandRequest;
use App\Models\Command;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;

class CommandController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(Command $model, $modelname = 'command')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $response = QueryBuilder::for(Command::class)
            ->with('category')
            ->allowedFilters(['command'])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'command'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreCommandRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->Category()->attach($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['category']);
    }

    public function update($id, StoreCommandRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);
        $model->Category()->sync($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!', ['id' => $model->id]);
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->Category()->detach();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    public function deleteMany(Request $request)
    {
        $ids = $request->input('ids');
        Command::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Commands deleted successfully'], 200);
    }
}
