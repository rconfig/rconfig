<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* AUTHENTICATION */

Auth::routes(['register' => false]);
Route::get('/logged-out', [LoginController::class, 'showLoggedOut']);

/* SOCIALITE AUTHENTICATION */
Route::get('/auth/saml2/metadata', function () {
    return Socialite::driver('saml2')->getServiceProviderMetadata();
});
Route::post('/auth/callback/{provider}', SocialiteController::class . '@callback');
Route::get('/auth/saml2/logout', function () {
    $response = Socialite::driver('saml2')->logoutResponse();
});
Auth::routes();
Route::get('auth/redirect/{provider}', SocialiteController::class . '@redirect');
Route::get('auth/callback/{provider}', SocialiteController::class . '@callback');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/download-export', 'App\Http\Controllers\FileDownloadController@download_export');
    Route::get('/download-import-template', 'App\Http\Controllers\FileDownloadController@download_device_import_template');

    /* SPA VUE ROUTE */
    Route::get('/{any}', [SpaController::class, 'index'])->where('any', '^(?!api\/)[\/\w\.-]*');
});
