<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\v1\ConfigsController as V1ConfigsController;

/**
 * @group Configurations
 *
 * @authenticated
 */
class ConfigsController extends V1ConfigsController
{
    // Inherits index/show/destroy plus search() and statuscount() from the v1 controller.
}
