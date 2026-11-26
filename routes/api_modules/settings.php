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

    /* REST API TOKENS. Admin only: every token has full Admin rights on the external API. */
    Route::resource('/rest-api-token', 'SettingRestApiTokenController')->only(['index', 'store', 'destroy'])
        ->middleware('admin');

    /* SETTINGS SPECIFIC ROUTES */
    Route::get('/get-timezone-list', 'SettingTimezoneController@getTimezoneList');
    Route::get('/test-email', 'SettingEmailController@TestMail');
    Route::get('/test-notification', 'SettingEmailController@TestMailNotifications');
    Route::get('/schedule/list', 'ScheduleController@list');

    /* SECURITY CENTER */
    Route::get('/security-review', 'SecurityReviewController@index');
    Route::post('/security-review/run', 'SecurityReviewController@run')->middleware('throttle:security-review-run');
    Route::get('/security-review/tasks/{token}', 'SecurityReviewController@task');
    Route::put('/security-review/notify', 'SecurityReviewController@updateNotify');

    /* CONFIG COMPARE OPTIONS */
    Route::get('/compare-options', 'CompareOptionsController@index');
    Route::get('/compare-options/default-template', 'CompareOptionsController@getDefaultTemplate');
    Route::patch('/compare-options/{id}', 'CompareOptionsController@update');

    /* DATA PURGE */
    Route::get('/purge', 'SettingsPurgeController@index')->name('purge.index');
    Route::get('/purge/{id}', 'SettingsPurgeController@show')->name('purge.show')->whereNumber('id');
    Route::patch('/purge/{id}', 'SettingsPurgeController@update')->name('purge.update')->whereNumber('id');
    Route::post('/purge-run-history/delete-many', 'PurgeRunController@deleteMany')->name('purge-run-history.delete-many');
    Route::get('/purge-run-history', 'PurgeRunController@index')->name('purge-run-history.index');
    Route::get('/purge-run-history/{id}', 'PurgeRunController@show')->name('purge-run-history.show')->whereNumber('id');
    Route::delete('/purge-run-history/{id}', 'PurgeRunController@destroy')->name('purge-run-history.destroy')->whereNumber('id');

    Route::prefix('/export')->group(function () {
        Route::get('/list-tables', 'ExportController@listTables');
        Route::get('/get-table/{table}', 'ExportController@export');
    });
    Route::prefix('/import')->group(function () {
        Route::post('/devices', 'DeviceImportController@importDeviceFile');
    });
});
