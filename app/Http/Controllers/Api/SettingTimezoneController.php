<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsTimezoneRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingTimezoneController extends ApiBaseController
{
    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function getTimezoneList()
    {
        return require 'timezone_list.php';
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }

    public function update($id, StoreSettingsTimezoneRequest $request): JsonResponse
    {
        $envPath = App::environmentFilePath();

        // The actual application timezone lives in .env (config('app.timezone')). If the web
        // server user cannot write it, the scheduler and dashboard keep using the old timezone
        // while the settings page shows the new one. Fail loudly with an actionable message
        // instead of silently leaving the timezones mismatched.
        if (! is_writable($envPath)) {
            return $this->failureResponse('The .env file is not writable by the web server, so the timezone could not be saved. Update the file permissions and try again.');
        }

        try {
            // The timezone is already validated against the timezone list, so it is safe to
            // pass through as-is. No slash escaping is needed for the .env file.
            Artisan::call('env:set', ['key' => 'TIMEZONE', 'value' => $request->timezone]);

            if (! App::environment('testing')) {
                Artisan::call('config:cache'); // cannot config:cache when testing; it triggers a full page reload on the front end
            }

            parent::updateResource($id, $request->toDTO()->toArray());

            // The dashboard system-info card caches config('app.timezone') for a week. Bust it
            // so the new timezone is reflected immediately instead of after the cache expires.
            Cache::forget('dashboard.sysinfo');

            return $this->successResponse('Timezone updated successfully');
        } catch (\Throwable $e) {
            return $this->failureResponse('Could not update the timezone: ' . $e->getMessage());
        }
    }
}
