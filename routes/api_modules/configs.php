<?php

use Illuminate\Support\Facades\Route;

Route::prefix('configs')->group(function () {
    Route::get('all-by-deviceid/{id}', 'ConfigController@getAllById');
    Route::get('latest-by-deviceid/{id}', 'ConfigController@getLatestById');
    Route::get('view-config/{id}', 'ConfigController@getSingleConfigFile');
    Route::get('distinct-commands/{id}', 'ConfigController@getDistinctCommands');
    Route::post('/search', 'ConfigSearchController@search');
    Route::post('/delete-many', 'ConfigController@deleteMany');
});

Route::resource('configs', 'ConfigController')->only(['index', 'show', 'destroy']);
