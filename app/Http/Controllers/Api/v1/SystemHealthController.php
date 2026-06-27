<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\SystemHealthController as BaseSystemHealthController;

/**
 * @group System Health
 *
 * @authenticated
 */
class SystemHealthController extends BaseSystemHealthController
{
    // Exposes healthLatest() from the application system health controller.
}
