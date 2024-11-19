<?php

/**
 * Loads & Stores device params in an object, device specific parameters as passed from rConfig database
 */

namespace App\Http\Controllers\Connections\Params;

use App\Models\DeviceCredentials;

class DeviceParams
{
    private $deviceParams;

    public function __construct(array $deviceRecord)
    {
        $this->deviceParams = $deviceRecord;
    }

    public function getAllDeviceParams()
    {
        if ($this->deviceParams['device_cred_id'] === 0) {
            return (object) $this->getDeviceParamsCombinedArray();
        }

        // if device_cred_id is 0, no device creds set, return devices own creds
        // else get credential set
        // then if cred has vault enabled get vault creds else get local device cred set
        if ($this->deviceParams['device_cred_id'] != 0) {
            $credential = DeviceCredentials::find($this->deviceParams['device_cred_id']);

            $this->setLocalCreds($credential);
        }

        return (object) $this->getDeviceParamsCombinedArray();
    }

    private function getDeviceParamsCombinedArray()
    {
        $deviceConnectionArray = $this->getDeviceConnectionparams();
        $deviceConnectionArray['deviceparams'] = $this->deviceParams;

        return $deviceConnectionArray;
    }

    private function getDeviceConnectionparams()
    {

        $template_data = new ConnectionParams($this->deviceParams['device_template']);

        return $template_data->getTemplateParams();
    }

    private function setLocalCreds($credential)
    {
        $this->deviceParams['device_username'] = $credential->cred_username;
        $this->deviceParams['device_password'] = $credential->cred_password;
        $this->deviceParams['device_enable_password'] = $credential->cred_enable_password;
    }
}
