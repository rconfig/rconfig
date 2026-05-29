<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsDeviceCredRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingDeviceCredController extends ApiBaseController
{
    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display the specified resource.
     *
     * @param  Setting  $id
     * @return Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Setting  $id
     * @return Response
     */
    public function update($id, StoreSettingsDeviceCredRequest $request)
    {
        return parent::updateResource($id, $request->toDTO()->toArray());
    }
}
