<?php

namespace App\Http\Controllers\Api;

use App\Traits\RespondsWithHttpStatus;
use Illuminate\Support\Facades\Config;

class SettingsSupportInfoController
{
    use RespondsWithHttpStatus;

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Setting  $id
     * @return \Illuminate\Http\Response
     */
    public function index($id, $relationship = null)
    {
        $result = [
            'version' => Config::get('app.version'),
            'rconfig_sub_id' => Config::get('rconfig_sub_id'),
            'rconfig_sub_name' => Config::get('rconfig_sub_name'),
            'rconfig_sub_status' => Config::get('rconfig_sub_status'),
            'rconfig_sub_expiry' => Config::get('rconfig_sub_expiry'),
        ];

        return $this->successResponse('License Information', $result);
    }
}
