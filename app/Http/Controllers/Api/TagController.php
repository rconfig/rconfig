<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
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
        $response = QueryBuilder::for(Tag::class)
            ->with(['device'])
            ->allowedFilters(['tagname'])
            ->defaultSort('-id')
            ->allowedSorts('id', 'tagname')
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreTagRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, null, ['device'], ['device']);
    }

    public function update($id, StoreTagRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }

    public function deleteMany(Request $request)
    {
        $ids = $request->input('ids');
        Tag::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Tags deleted successfully'], 200);
    }
}
