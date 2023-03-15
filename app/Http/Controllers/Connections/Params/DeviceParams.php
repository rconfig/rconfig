<?php

/**
 * Loads & Stores device params in an object, device specific parameters as passed from rConfig database
 */

namespace App\Http\Controllers\Connections\Params;

class DeviceParams
{
    private $deviceParams;

    public function __construct(array $deviceRecord)
    {
        $this->deviceParams = $deviceRecord;
    }

    public function getAllDeviceParams()
    {
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
}
