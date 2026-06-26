<?php

namespace App\Services\ConfigCompare;

use App\Models\Config;
use App\Models\ConfigChange;
use App\Models\Setting;

class ConfigComparer
{
    /**
     * Compare two cleaned config files and, when a real difference remains,
     * persist a ConfigChange record holding the rendered diff.
     *
     * @return array<string, mixed>
     */
    public function compare(string $cleanedFileOld, string $cleanedFileNew, Config $model, Config $latestConfig): array
    {
        $cc = new ConfigCompareService($cleanedFileOld, $cleanedFileNew);
        $res = $cc->file_content_compare();

        // Guard: any error returned as array
        if (isset($res['error'])) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $res['error'], 'config', $model->device_name, $model->device_id, $model->device_category, $model->id);

            return $res;
        }

        // Guard: completely empty diff
        if (empty($res)) {
            return ['error' => 'Checksum different but no diff found.'];
        }

        // If no differences remain after exclusions, do not log a change
        if (($res['diff_type'] ?? null) === 'none') {
            return $res;
        }

        $compareExclusionSettings = Setting::where('id', 1)
            ->select('config_compare_settings', 'config_compare_exclusion_file')
            ->first()
            ?->toArray();

        ConfigChange::create([
            'current_config_id' => $model->id,
            'previous_config_id' => $latestConfig->id,
            'config_version' => $model->config_version,
            'config_change_type' => $res['diff_type'] ?? 'changed',
            'config_diff' => $res['diff'],
            'compare_exclusion_settings' => $compareExclusionSettings,
            'change_trigger' => 'pull',
        ]);

        return $res;
    }
}
