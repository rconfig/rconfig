<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External REST API - Version 2
|--------------------------------------------------------------------------
|
| Token-authenticated public API. Registered under the "api/v2" prefix with
| the "apiv2auth" middleware (see App\Providers\RouteServiceProvider). All
| controllers live in the App\Http\Controllers\Api\v2 namespace.
|
*/

Route::get('/apitest', 'ApiTestController@index')->name('apitest.index');

/* DASHBOARD / SYSTEM */
Route::get('/dashboard/health-latest', 'SystemHealthController@healthLatest')->name('dashboard.health-latest');

/* CONFIGURATIONS */
Route::match(['get', 'post'], '/configs/search', 'ConfigsController@search')->name('configs.search');
Route::get('/configs/status/{deviceid}/{status}', 'ConfigsController@statuscount')->name('configs.status');
Route::apiResource('configs', 'ConfigsController')->only(['index', 'show', 'destroy']);

/* CONFIG CHANGES */
Route::get('/config-changes/by-config/{config}', 'ConfigChangesController@byConfig')->name('config-changes.by-config');
Route::get('/config-changes/{id}', 'ConfigChangesController@show')->name('config-changes.show');

/* DEVICES */
Route::get('/devices/summary', 'DeviceController@summary')->name('devices.summary');
Route::post('/devices/{id}/enable', 'DeviceController@enable')->name('devices.enable');
Route::post('/devices/{id}/disable', 'DeviceController@disable')->name('devices.disable');
Route::apiResource('devices', 'DeviceController');

/* USERS */
Route::apiResource('users', 'UserController');
