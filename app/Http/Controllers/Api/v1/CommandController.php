<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\CommandController as BaseCommandController;

/**
 * @group Commands
 *
 * @authenticated
 */
class CommandController extends BaseCommandController
{
    // Inherits index/show/store/update/destroy from the application command controller.
}
