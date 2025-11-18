<?php

namespace App\Http\Controllers\Api;

use App\Models\IntegrationConfigured;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;

class IntegrationConfiguredController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(IntegrationConfigured $model, $modelname = 'integration_configured')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        //$this->authorize('integrationconfigured.view');

        $searchCols = ['name'];

        // Get main integration data
        $integrations = parent::index($request, $searchCols, ['integrationoption']);

        // Collect authentication service configurations
        $authServices = [
            'okta' => [
                'name' => 'Okta SSO',
                'is_configured' => ! empty(config('services.okta.client_id')),
            ],
            'microsoft' => [
                'name' => 'Microsoft SSO',
                'is_configured' => ! empty(config('services.microsoft.client_id')),
            ],
            'google' => [
                'name' => 'Google SSO',
                'is_configured' => ! empty(config('services.google.client_id')),
            ],
            'saml2' => [
                'name' => 'SAML2',
                'is_configured' => ! empty(config('services.saml2.sp_acs')),
            ],
        ];

        // Create the response structure
        $response = [
            'integrations' => $integrations,
            'auth_services' => $authServices,
        ];

        return response()->json($response);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        // $this->authorize('integrationconfigured.view');

        $result = parent::show($id, ['integrationoption']);

        return response()->json($result);
    }
}
