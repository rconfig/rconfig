<?php

use App\Http\Controllers\Api\CommandController;
use Illuminate\Support\Facades\Route;

Route::get('/categories/list', 'CategoryController@multiSelectIndex')->name('categories.list');
Route::post('/categories/delete-many', 'CategoryController@deleteMany');

Route::resource('categories', 'CategoryController');
Route::controller(CommandController::class)->prefix('commands')->group(function () {
    Route::post('/bulk-update-categories', 'bulkUpdateCats');
});