<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends ApiBaseController
{
    public function __construct(User $model, $modelname = 'user')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $response = QueryBuilder::for(User::class)
            ->allowedFilters(['name'])
            ->defaultSort('-id')
            ->allowedSorts(['id', 'name', 'email', 'last_login'])
            ->paginate((int) $request->perPage);

        return response()->json($response);
    }

    public function store(StoreUserRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreUserRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }
}
