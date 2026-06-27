<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\DeviceController as BaseDeviceController;

/**
 * @group Devices
 *
 * @authenticated
 */
class DeviceController extends BaseDeviceController
{
    // Inherits index/show/store/update/destroy (plus credential masking) from the
    // application device controller.
}
