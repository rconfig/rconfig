<?php

use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::resource('/settings', 'SettingBannerController')->only(['show']);
    Route::resource('/banner', 'SettingBannerController')->only(['show', 'update']);
    Route::resource('/timezone', 'SettingTimezoneController')->only(['show', 'update']);
    Route::resource('/email', 'SettingEmailController')->only(['show', 'update']);
    Route::post('/credentials/delete-many', 'DeviceCredentialsController@deleteMany');
    Route::resource('/defaultcred', 'SettingDeviceCredController')->only(['show', 'update']);
    Route::resource('/credentials', 'DeviceCredentialsController');
    Route::resource('/debug', 'SettingDebugController')->only(['show', 'update']);
    Route::get('/socialite-status', 'SettingSSOController@show');

    /* REST API TOKENS */
    Route::resource('/rest-api-token', 'SettingRestApiTokenController')->only(['index', 'store', 'destroy']);

    /* SETTINGS SPECIFIC ROUTES */
    Route::get('/get-timezone-list', 'SettingTimezoneController@getTimezoneList');
    Route::get('/test-email', 'SettingEmailController@TestMail');
    Route::get('/test-notification', 'SettingEmailController@TestMailNotifications');
    Route::get('/schedule/list', 'ScheduleController@list');

    /* CONFIG COMPARE OPTIONS */
    Route::get('/compare-options', 'CompareOptionsController@index');
    Route::get('/compare-options/default-template', 'CompareOptionsController@getDefaultTemplate');
    Route::patch('/compare-options/{id}', 'CompareOptionsController@update');

    Route::prefix('/export')->group(function () {
        Route::get('/list-tables', 'ExportController@listTables');
        Route::get('/get-table/{table}', 'ExportController@export');
    });
    Route::prefix('/import')->group(function () {
        Route::post('/devices', 'DeviceImportController@importDeviceFile');
    });
});
