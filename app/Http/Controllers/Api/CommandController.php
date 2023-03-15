<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCommandRequest;
use App\Models\Command;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommandController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(Command $model, $modelname = 'command')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['command'];

        return response()->json(parent::index($request, $searchCols, ['category']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCommandRequest $request)
    {
        $model = parent::storeResource($request->toDTO()->toArray(), 1);

        $model->Category()->attach($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Command  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['category']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Command  $tag
     * @return \Illuminate\Http\Response
     */
    public function update($id, StoreCommandRequest $request)
    {
        $model = parent::updateResource($id, $request->toDTO()->toArray(), 1);
        $model->Category()->sync($request->categoryArray);

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $model->Category()->detach();

        return $this->successResponse(Str::ucfirst($this->modelname) . ' deleted successfully!');
    }
}
