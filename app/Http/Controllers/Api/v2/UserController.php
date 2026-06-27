<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Api\UserController as BaseUserController;

/**
 * @group Users
 *
 * @authenticated
 */
class UserController extends BaseUserController
{
    // Inherits index/show/store/update/destroy from the application user controller.
}
