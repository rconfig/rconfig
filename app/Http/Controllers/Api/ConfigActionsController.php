<?php

namespace App\Http\Controllers\Api;

use App\Jobs\DownloadConfigNow;
use App\Jobs\PurgeFailedConfigsJob;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigActionsController extends ApiBaseController
{

    protected $model;
    protected $modelname;

    public function __construct(Config $model, $modelname = 'config')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function downloadNow(Request $request)
    {
        $username = Auth::user()->name;

        if (App()->environment('testing')) { // required for testing
            dispatch(new DownloadConfigNow($request->device_id, $username))->onConnection('sync');
        } else {
            dispatch(new DownloadConfigNow($request->device_id, $username))->onQueue('downloadqueue');
        }

        return $this->successResponse('Download started');
    }


    public function purgeFailed(Request $request)
    {
        if (App()->environment('testing')) { // required for testing
            dispatch(new PurgeFailedConfigsJob($request->device_id))->onConnection('sync');
        } else {
            dispatch(new PurgeFailedConfigsJob($request->device_id))->onQueue('lastqueue');
        }

        return $this->successResponse('Purge started');
    }
}
