<?php

namespace App\Http\Controllers\Api;

use App\Models\IntegrationOption;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;

class IntegrationOptionController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(IntegrationOption $model, $modelname = 'integration_option')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        // $this->authorize('integrationoption.view');

        $searchCols = ['name'];

        return response()->json(parent::index($request, $searchCols, []));
    }
}
