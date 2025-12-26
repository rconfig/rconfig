<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsDebugRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

class SettingDebugController extends ApiBaseController
{
    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreSettingsDebugRequest $request)
    {

        $phpDebuggingStr = $request->phpDebugging ? 'true' : 'false';
        Artisan::call('env:set APP_DEBUG="' . $phpDebuggingStr . '"');
        if (! App()->environment('testing')) {
            Artisan::call('config:cache'); // cannot to a config:cache when testing
        }

        return parent::updateResource($id, $request->toDTO()->toArray());
    }
}
