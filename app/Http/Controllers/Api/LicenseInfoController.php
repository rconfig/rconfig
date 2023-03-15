<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\RespondsWithHttpStatus;

class LicenseInfoController extends Controller
{
    use RespondsWithHttpStatus;

    public function index()
    {
        $license = [
            'version' => config('app.version'),
            'sub_id' => config('app.rconfig_sub_id'),
            'sub_name' => config('app.rconfig_sub_name'),
            'status' => config('app.rconfig_sub_status'),
            'expiry' => config('app.rconfig_sub_expiry'),
        ];

        return $this->successResponse('Success', $license);
    }
}
