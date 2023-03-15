<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends ApiBaseController
{
    public function __construct(Tag $model, $modelname = 'tag')
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
        $searchCols = ['tagname'];

        return response()->json(parent::index($request, $searchCols, null, ['device'], ['device']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTagRequest $request)
    {
        return parent::storeResource($request->toDTO()->toArray());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id, null, ['device'], ['device']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update($id, StoreTagRequest $request)
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
