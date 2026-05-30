<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Generates the device import template (header row only) so users always get a
 * template whose columns match exactly what the importer expects.
 */
class DeviceImportTemplateExport implements FromArray, WithHeadings
{
    /**
     * The columns understood by the device importer.
     */
    public const COLUMNS = [
        'device_name',
        'device_ip',
        'device_username',
        'device_password',
        'device_enable_password',
        'device_main_prompt',
        'device_enable_prompt',
        'device_category_id',
        'device_template',
        'device_model',
        'device_vendor',
        'device_tag',
        'device_default_creds_on',
        'device_cred_id',
    ];

    /**
     * @return array<int, mixed>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return self::COLUMNS;
    }
}
