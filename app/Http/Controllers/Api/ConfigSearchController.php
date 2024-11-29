<?php

namespace App\Http\Controllers\Api;

use App\CustomClasses\ConfigSearch;
use App\Http\Controllers\Controller;
use App\Services\Config\Search\SearchStrategies\LatestSearchStrategyNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConfigSearchController extends Controller
{
    public function search(Request $request)
    {
        Validator::make($request->all(), [
            // 'device_name' => 'required',
            // 'device_category' => '',
            'command' => 'required',
            'search_string' => 'required',
            // 'lines_before' => 'required',
            // 'lines_after' => 'required',
            // 'latest_version_only' => 'required',
            // 'start_date' => 'required',
            // 'end_date' => 'required',
            // 'ignore_case' => 'required',
        ])->validate();


        $result = (new LatestSearchStrategyNew())->searchConfigurations($request['device_name'], $request['device_category'], $request['command'], $request['search_string'], $request['lines_before'], $request['lines_after'], $request['latest_version_only'] == 'true' ? true : false, $request['start_date'], $request['end_date'], $request['ignore_case'] == 'true' ? true : false);

        return json_encode($result);
    }
}
