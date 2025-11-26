<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommandRequest;
use App\Models\Command;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
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

        $perPage = (int) $request->perPage ?: 10;

        $query = QueryBuilder::for(Command::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'command'),
            ])
            ->allowedSorts(['id', 'command', 'created_at'])
            ->with('category');

        $result = $query->paginate($perPage);

        return response()->json($result);
    }

    public function store(StoreCommandRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->Category()->attach($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!');
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['category']);
    }

    public function update($id, StoreCommandRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);
        $model->Category()->sync($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!');
    }

    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->Category()->detach();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }

    public function bulkUpdateCats(Request $request)
    {
        $commands = $request->input('commands');

        if (! $commands) {
            return $this->failureResponse('No commands found');
        }

        $categories = $request->input('categories');
        if (! $categories) {
            return $this->failureResponse('No categories found');
        }
        // get ids only form $categories
        $categories = array_map(function ($category) {
            return $category['id'];
        }, $categories);

        foreach ($commands as $command) {
            $command = Command::find($command['id']);
            $command->Category()->sync($categories);
        }

        return response()->json(['status' => 'success']);
    }
}
