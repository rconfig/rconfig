<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\DashboardController as BaseDashboardController;

/**
 * @group Dashboard
 *
 * @authenticated
 */
class DashboardController extends BaseDashboardController
{
    // Exposes getSysInfo() and getConfigInfo() from the application dashboard controller.
}
