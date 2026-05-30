<?php

namespace App\Imports;

use App\Models\Device;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// https://docs.laravel-excel.com/3.1/imports/

class DevicesImport implements ToModel, WithHeadingRow
{
    private $rows = 0;

    /**
     * @param  array<string, mixed>  $row
     * @return Model|null
     */
    public function model(array $row)
    {
        $this->rows++;

        return new Device([
            'device_name' => $row['device_name'],
            'device_ip' => $row['device_ip'],
            'device_username' => $row['device_username'],
            'device_password' => $row['device_password'],
            'device_enable_password' => $row['device_enable_password'],
            'device_main_prompt' => $row['device_main_prompt'],
            'device_enable_prompt' => $row['device_enable_prompt'],
            'device_category_id' => $row['device_category_id'],
            'device_template' => $row['device_template'],
            'device_model' => $row['device_model'],
            'device_vendor' => $row['device_vendor'],
        ]);
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}
