<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TagController extends ApiBaseController
{
    public function __construct(Tag $model, $modelname = 'tag')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null, $return = 0)
    {
        $perPage = (int) $request->perPage ?: 10;

        $query = QueryBuilder::for(Tag::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, 'tagname'),
            ])
            ->allowedSorts(['id', 'tagname', 'created_at'])
            ->with(['device:id,device_name']);

        $result = $query->paginate($perPage);

        return response()->json($result);
    }

    public function store(StoreTagRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!', ['id' => $model->id]);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, null, ['device'], ['device']);
    }

    public function update($id, StoreTagRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!', ['id' => $model->id]);
    }

    public function destroy($id, $return = 0)
    {

        $model = parent::destroy($id, 1);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!', ['id' => $model->id]);
    }
}
