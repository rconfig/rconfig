<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\TaskController as BaseTaskController;

/**
 * @group Tasks
 *
 * @authenticated
 */
class TaskController extends BaseTaskController
{
    // Inherits index/show/store/update/destroy from the application task controller.
}
