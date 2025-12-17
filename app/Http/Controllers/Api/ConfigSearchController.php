<?php

namespace App\Http\Controllers\Api;

use App\CustomClasses\ConfigSearch;
use App\Http\Controllers\Controller;
use App\Services\Config\Search\SearchStrategies\LatestSearchStrategyNew;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConfigSearchController extends Controller
{
    use RespondsWithHttpStatus;


    public function search(Request $request)
    {
        // Validate request data
        Validator::make($request->all(), [
            'command' => 'required',
            'search_string' => 'required',
            'latest_version_only' => 'required|boolean',
            'ignore_case' => 'required|boolean',
        ])->validate();

        try {
            $result = (new LatestSearchStrategyNew)->searchConfigurations($request->toArray());
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $e->getMessage(), 'config');
            return $this->failureResponse('An error occurred while searching configurations. Check the logs.', 500);
        }

        return $this->successResponse('Search results', $result);
    }
}
