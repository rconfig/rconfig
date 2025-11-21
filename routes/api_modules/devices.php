<?php

use Illuminate\Support\Facades\Route;

Route::resource('devices', 'DeviceController');
Route::post('/devices/delete-many', 'DeviceController@deleteMany');

Route::prefix('device')->group(function () {
    Route::post('/download-now', 'ConfigActionsController@downloadNow');
    Route::post('/download-many', 'ConfigActionsController@downloadMany');
    Route::post('/purge-failed-configs', 'ConfigActionsController@purgeFailed');
    Route::get('/all-device-names', 'DeviceController@allDeviceNames');
    Route::get('/disable/{id}', 'DeviceController@disable');
    Route::get('/enable/{id}', 'DeviceController@enable');
    Route::post('/bulk-update/{type}', 'DeviceBulkUpdateController@bulkUpdate');
});

/* DEVICES SPECIFIC ROUTES */
Route::get('/get-devices-filter-list', 'DeviceController@multiSelectIndex'); // for use in DeviceMultiSelect.vue
