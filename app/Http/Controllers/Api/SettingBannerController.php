<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsBannerRequest;
use App\Models\Setting;

class SettingBannerController extends ApiBaseController
{
    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, StoreSettingsBannerRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }
}
