<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompareOptionsRequest;
use App\Models\Setting;
use App\Services\Templates\CompareExclusionTemplateService;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;

class CompareOptionsController extends Controller
{
    use RespondsWithHttpStatus;

    public function index(Request $request)
    {
        $response = Setting::where('id', 1)
            ->select('config_compare_settings', 'config_compare_exclusion_file')
            ->first();

        // Seed defaults on first use so the UI always has something to show.
        if ($response && empty($response->config_compare_settings)) {
            (new CompareExclusionTemplateService)->installDefaultTemplate();
            $response = Setting::where('id', 1)
                ->select('config_compare_settings', 'config_compare_exclusion_file')
                ->first();
        }

        return $this->successResponse('Success', $response);
    }

    public function update($id, StoreCompareOptionsRequest $request)
    {
        $compareSettingsArr = $request->validated();
        $exclusionFileContent = $compareSettingsArr['config_compare_exclusion_file'] ?? null;
        unset($compareSettingsArr['config_compare_exclusion_file']);

        $setting = Setting::findOrFail($id);
        $setting->update([
            'config_compare_settings' => $compareSettingsArr,
            'config_compare_exclusion_file' => $exclusionFileContent,
        ]);

        return $this->successResponse('Successfully updated compare options', $setting->id);
    }

    public function getDefaultTemplate()
    {
        $templateString = (new CompareExclusionTemplateService)->getTemplateString();

        return $this->successResponse('Successfully set default compare options', $templateString);
    }
}
