<?php

namespace App\Http\Controllers\Api;

use App\Models\ConfigChange;

class ConfigChangesController extends ApiBaseController
{
    public function __construct(ConfigChange $model, $modelname = 'configchange')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Return the change record (including the rendered diff) for a given
     * "current" config id. Powers the per-config changes panel.
     */
    public function showByCurrentConfigId($id)
    {
        return ConfigChange::where('current_config_id', $id)->first();
    }
}
