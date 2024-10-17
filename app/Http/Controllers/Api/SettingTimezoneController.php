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

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreSettingsTimezoneRequest $request)
    {
        $timezone = str_replace('/', '\/\\', $request->timezone);
        Artisan::call('env:set TIMEZONE="' . $timezone . '"');
        if (! App()->environment('testing')) {
            Artisan::call('config:cache'); // cannot to a config:cache when testing. This causes a full page reload on the front end
        }

        return parent::updateResource($id, $request->toDTO()->toArray());
    }
}
