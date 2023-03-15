<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends ApiBaseController
{
    public function __construct(Category $model, $modelname = 'category')
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
        $searchCols = ['categoryName'];

        return response()->json(parent::index($request, $searchCols, ['command'], ['device']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, ['command'], ['device']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $tag
     * @return \Illuminate\Http\Response
     */
    public function update($id, StoreCategoryRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        return parent::destroy($id);
    }
}
