<?php

use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::resource('/settings', 'SettingBannerController')->only(['show']);
    Route::resource('/banner', 'SettingBannerController')->only(['show', 'update']);
    Route::resource('/timezone', 'SettingTimezoneController')->only(['show', 'update']);
    Route::resource('/email', 'SettingEmailController')->only(['show', 'update']);
    Route::get('/socialite-status', 'SettingSSOController@show');

    /* SETTINGS SPECIFIC ROUTES*/
    Route::get('/get-timezone-list', 'SettingTimezoneController@getTimezoneList');
    Route::get('/test-email', 'SettingEmailController@TestMail');
});