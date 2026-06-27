<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\CategoryController as BaseCategoryController;

/**
 * @group Categories
 *
 * @authenticated
 */
class CategoryController extends BaseCategoryController
{
    // Inherits index/show/store/update/destroy from the application category controller.
}
