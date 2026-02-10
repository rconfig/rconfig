<?php

namespace App\Http\Controllers\Api;

use App\Exports\DeviceInventoryExport;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DeviceInventoryExporter extends Controller
{
    use RespondsWithHttpStatus;

    public function export(Request $request)
    {
        $excludeFields = ['device_password', 'device_enable_password'];

        $devices = $this->fetchDevices($excludeFields);


        if ($devices->isEmpty()) {
            return $this->failureResponse('No device inventory data found', 422);
        }

        $filename = $this->generateFileName();

        if ($this->exportToFile($devices->toArray(), $filename)) {
            $downloadLink = $this->generateDownloadLink($filename);

            return $this->successResponse('Exported device inventory to ' . $filename, ['downloadLink' => $downloadLink], 200);
        } else {
            return $this->failureResponse('Error exporting file', 500);
        }
    }

    private function fetchDevices(array $excludeFields)
    {
        return Device::get()
            ->sortBy('id')
            ->map(function ($device) use ($excludeFields) {
                return $this->transformDeviceData($device, $excludeFields);
            });
    }

    private function transformDeviceData($device, array $excludeFields)
    {
        $deviceData = [
            'Device ID' => $device->id,
            'Device Name' => $device->device_name,
            'Device Port' => $device->device_port_override,
            'Device Credential ID' => $device->device_cred_id,
            'Device Username' => $device->device_username,
            'Device IP' => $device->device_ip,
            'Device Main Prompt' => $device->device_main_prompt,
            'Device Enable Prompt' => $device->device_enable_prompt,
            'Device Category' => isset($device->category[0]) ? $device->category[0]->categoryName : '',
            'Device Template' => isset($device->template[0]) ? $device->template[0]->templateName : '',
            'Model' => $device->device_model,
            'Status' => $device->status,
            'Created At' => $device->created_at ? $device->created_at->format('d-m-Y H:i:s') : null,
            'Updated At' => $device->updated_at ? $device->updated_at->format('d-m-Y H:i:s') : null,
            'Last Seen At' => $device->last_seen_at ? $device->last_seen_at->format('d-m-Y H:i:s') : null,
        ];

        return array_diff_key($deviceData, array_flip($excludeFields));
    }

    private function generateFileName()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return 'device_inventory_' . $timestamp . '.csv';
    }

    private function exportToFile(array $devices, string $filename)
    {
        $exported = Excel::store(new DeviceInventoryExport($devices), $filename, 'rconfig_exports');

        if ($exported) {
            $logmsg = $filename . ' exported ' ;
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'export');
        }

        return $exported;
    }

    private function generateDownloadLink(string $filename)
    {
        return '<a href="/download-export?filename=' . basename($filename) . '&type=export" class="card-pf-link-with-icon alink">  <span class="fa fa-arrow-circle-o-down"></span>Download File: ' . basename($filename) . '</a> ';
    }
}
