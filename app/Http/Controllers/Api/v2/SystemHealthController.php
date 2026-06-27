<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\SystemHealthController as BaseSystemHealthController;

/**
 * @group Dashboard
 *
 * @authenticated
 */
class SystemHealthController extends BaseSystemHealthController
{
    // Exposes healthLatest() from the application system health controller.
}
