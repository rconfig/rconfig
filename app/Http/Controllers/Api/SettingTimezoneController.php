<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsTimezoneRequest;
use App\Models\Setting;
use Artisan;

class SettingTimezoneController extends ApiBaseController
{
    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function getTimezoneList()
    {
        return require 'timezone_list.php';
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
    public function update($id, StoreSettingsTimezoneRequest $request)
    {
        $timezone = str_replace('/', '\/\\', $request->timezone);
        Artisan::call('env:set TIMEZONE="'.$timezone.'"');
        if (! App()->environment('testing')) {
            Artisan::call('config:cache'); // cannot to a config:cache when testing
        }

        return parent::updateResource($id, $request->toDTO()->toArray());
    }
}
