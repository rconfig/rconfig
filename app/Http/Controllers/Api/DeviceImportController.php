<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\DevicesImport;
use App\Models\Category;
use App\Models\Device;
use App\Models\DeviceCredentials;
use App\Models\Tag;
use App\Models\Template;
use App\Models\Vendor;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class DeviceImportController extends Controller
{
    use RespondsWithHttpStatus;

    protected Device $model;
    protected string $modelname;

    public function __construct(Device $model, string $modelname = 'device')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function importDeviceFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx|max:2048',
        ]);

        if (! $request->hasFile('file')) {
            $logmsg = 'Could not import file!';
            activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'import');

            return $this->failureResponse($logmsg);
        }

        $import = new DevicesImport;
        $devices = Excel::toCollection($import, $request->file('file'));
        $sanitizedDevices = $this->checkUploadedDeviceFields($devices[0]);

        foreach ($sanitizedDevices['gooddevices'] as $device) {
            $this->store_device($device);
        }

        $rowCount = $sanitizedDevices['gooddevices']->count();
        $logmsg = $rowCount . ' Devices successfully imported';
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'import');

        return $this->successResponse($logmsg, ['errorlog' => $sanitizedDevices['baddevices']->pluck('import_note')], 200);
    }

    /**
     * Validate each uploaded row and split it into good and bad devices.
     *
     * @return array{gooddevices: Collection, baddevices: Collection}
     */
    public function checkUploadedDeviceFields(Collection $devices): array
    {
        $baddevices = collect();
        $gooddevices = collect();

        foreach ($devices as $device) {
            $this->filterForValidColumnsOnly($device); // remove any blank columns/ cells if present

            $requiredColumns = ['device_name', 'device_ip', 'device_model', 'device_category_id', 'device_template', 'device_main_prompt', 'device_enable_prompt', 'device_tag', 'device_vendor'];

            if (in_array(null, array_intersect_key($device->toArray(), array_flip($requiredColumns)))) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Blank Fields';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            if (! isset($device['device_default_creds_on'])) {
                // work around in case device_default_creds_on is missing from the import template
                $device['device_default_creds_on'] = 0;
            }

            if (! DeviceCredentials::where('id', $device['device_cred_id'])->exists() && $device['device_default_creds_on'] == 1) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Invalid Credentials ID';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            if (! Category::where('id', $device['device_category_id'])->exists()) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Invalid Category ID';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            if (! Vendor::where('id', $device['device_vendor'])->exists()) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Invalid Vendor ID';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            if (! Template::where('id', $device['device_template'])->exists()) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Invalid Template ID';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            if (! Tag::whereIn('id', explode(',', $device['device_tag']))->exists()) {
                $device['import_note'] = 'Import Error ' . $device['device_name'] . ': Invalid Tag ID';
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $device['import_note'], 'import');
                $baddevices->push($device);

                continue;
            }

            $gooddevices->push($device);
        }

        return ['gooddevices' => $gooddevices, 'baddevices' => $baddevices];
    }

    public function store_device($device)
    {
        $model = new Device;

        if ($device['device_default_creds_on'] == 1) {
            $model->device_cred_id = $device['device_cred_id'];
            $creds = DeviceCredentials::where('id', $device['device_cred_id'])->first();
            $model->device_username = $creds->cred_username;
            $model->device_password = $creds->cred_password;
            $model->device_enable_password = $creds->cred_enable_password;
        } else {
            $model->device_username = $device['device_username'] ?? null;
            $model->device_password = $device['device_password'] ?? null;
            $model->device_enable_password = $device['device_enable_password'] ?? null;
        }

        // needed due to separate logic
        $model->device_name = $this->sanitiseDevicename($device['device_name']);
        $model->device_ip = $device['device_ip'];
        $model->device_model = $device['device_model'];
        $model->device_category_id = $device['device_category_id'];
        $model->device_default_creds_on = $device['device_default_creds_on'] ?? null;
        $model->device_cred_id = $device['device_cred_id'] ?? null;
        $model->device_template = $device['device_template'];
        $model->device_main_prompt = $device['device_main_prompt'];
        $model->device_enable_prompt = $device['device_enable_prompt'];
        $model->status = 2; // newly added devices via the import should have status set to 2. Git issue #159

        $model->save();
        $model->tag()->attach(explode(',', $device['device_tag']));
        $model->vendor()->attach($device['device_vendor']);
        $model->category()->attach($device['device_category_id']);
        $model->template()->attach($device['device_template']);

        $msg = 'Imported ' . $device['device_name'] . ' successfully';
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg, 'import');

        return $this->successResponse($msg, ['id' => $model->id], 200);
    }

    public function filterForValidColumnsOnly($device)
    {
        // check if key are in array
        $validColumns = ['device_name', 'device_ip', 'device_model', 'device_category_id', 'device_template', 'device_main_prompt', 'device_enable_prompt', 'device_tag', 'device_vendor', 'device_username', 'device_password', 'device_enable_password', 'device_default_creds_on', 'device_cred_id'];

        // unset invalid columns from array
        foreach ($device as $key => $value) {
            if (! in_array($key, $validColumns)) {
                unset($device[$key]);
            }
        }

        return $device;
    }

    public function sanitiseDevicename(string $device_name): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '', $device_name);
    }
}
