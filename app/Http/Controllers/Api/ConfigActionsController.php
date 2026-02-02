<?php

namespace App\Http\Controllers\Api;

use App\Jobs\DownloadConfigNowJob;
use App\Jobs\PurgeFailedConfigsJob;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserLog\UserLogActivity;

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
            dispatch(new DownloadConfigNowJob($request->device_id, $username))->onConnection('sync');
        } else {
            dispatch(new DownloadConfigNowJob($request->device_id, $username))->onQueue('ManualDownloadQueue');
        }

        return $this->successResponse('Download started');
    }

    public function downloadMany(Request $request)
    {

        if (! is_array($request->device_ids) || empty($request->device_ids)) {
            return $this->failureResponse('Device IDs must be an array', 422);
        }

        $username = Auth::user()->name;

        foreach ($request->device_ids as $device_id) {
            if (! is_int($device_id)) {
                return $this->failureResponse('Device IDs must be integers', 422);
            }

            if (App()->environment('testing')) { // required for testing
                dispatch(new DownloadConfigNowJob($device_id, $username))->onConnection('sync');
            } else {
                dispatch(new DownloadConfigNowJob($device_id, $username))->onQueue('ManualDownloadQueue');
            }
        }

        // UserLogActivity::addToLog('User started download for devices: ' . implode(', ', $request->device_ids));

        return $this->successResponse('Download started for devices: ' . implode(', ', $request->device_ids));
    }

    public function purgeFailed(Request $request)
    {

        if (App()->environment('testing')) { // required for testing
            dispatch(new PurgeFailedConfigsJob($request->device_id))->onConnection('sync');
        } else {
            dispatch(new PurgeFailedConfigsJob($request->device_id))->onQueue('rConfigDefault');
        }

        return $this->successResponse('Purge started');
    }
}
