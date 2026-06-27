<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External REST API - Version 1
|--------------------------------------------------------------------------
|
| Token-authenticated public API. Registered under the "api/v1" prefix with
| the "apiv1auth" middleware (see App\Providers\RouteServiceProvider). All
| controllers live in the App\Http\Controllers\Api\v1 namespace.
|
*/

Route::get('/apitest', 'ApiTestController@index')->name('apitest.index');

/* DASHBOARD / SYSTEM */
Route::get('/sysinfo', 'DashboardController@getSysInfo')->name('sysinfo');
Route::get('/configinfo', 'DashboardController@getConfigInfo')->name('configinfo');
Route::get('/health-latest', 'SystemHealthController@healthLatest')->name('health-latest');

/* CONFIGURATION ACTIONS */
Route::get('/download-now/{id}', 'ConfigActionsController@apiDownloadNow')->name('download-now');

/* CONFIGURATIONS */
Route::match(['get', 'post'], '/configs/search', 'ConfigsController@search')->name('configs.search');
Route::get('/configs/all-by-deviceid/{id}', 'ConfigsController@allByDeviceId')->name('configs.all-by-deviceid');
Route::get('/configs/status/{deviceid}/{status}', 'ConfigsController@statuscount')->name('configs.status');
Route::apiResource('configs', 'ConfigsController')->only(['index', 'show', 'destroy']);

/* DEVICES */
Route::apiResource('devices', 'DeviceController');
Route::apiResource('device-credentials', 'DeviceCredentialsController');

/* MASTER DATA */
Route::apiResource('templates', 'TemplateController');
Route::apiResource('categories', 'CategoryController');
Route::apiResource('commands', 'CommandController');
Route::apiResource('vendors', 'VendorController');
Route::apiResource('tags', 'TagController');

/* TASKS */
Route::apiResource('tasks', 'TaskController');

/* USERS */
Route::apiResource('users', 'UserController');
