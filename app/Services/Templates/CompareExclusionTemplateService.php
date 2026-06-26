<?php

namespace App\Services\Templates;

use App\Models\Setting;

class CompareExclusionTemplateService
{
    public function getTemplateString(): string
    {
        return '// Description: This is the global exclusion Policy list
#[global]
/^Current configuration.*$/m
/^.*Last configuration change.*$/m

// Description: This is the Show Run specific Exclusion List
#[show run]

// Description: This is the Show Version specific Exclusion List
#[show version]';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultCompareSettings(): array
    {
        return [
            'context' => 3,
            'ignoreCase' => false,
            'ignoreLineEnding' => false,
            'ignoreWhitespace' => false,
            'lengthLimit' => 20000,
        ];
    }

    public function installDefaultTemplate(): void
    {
        $setting = Setting::find(1);

        if (! $setting) {
            return;
        }

        $setting->update([
            'config_compare_settings' => $this->getDefaultCompareSettings(),
            'config_compare_exclusion_file' => $this->getTemplateString(),
        ]);
    }
}
