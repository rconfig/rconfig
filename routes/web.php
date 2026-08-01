<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* AUTHENTICATION */

/*
 * Accounts in rConfig are admin provisioned or created through SSO, so self
 * service registration is deliberately disabled. Do not add a second, bare
 * Auth::routes() call anywhere in this file. Laravel does not deduplicate or
 * retract routes, so a later call silently re-enables `register` and every new
 * account lands on the users.role column default. See RegistrationDisabledTest.
 */
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
Route::get('auth/redirect/{provider}', SocialiteController::class . '@redirect');
Route::get('auth/callback/{provider}', SocialiteController::class . '@callback');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/download-export', 'App\Http\Controllers\FileDownloadController@download_export');
    Route::get('/download-import-template', 'App\Http\Controllers\FileDownloadController@download_device_import_template');

    /* SPA VUE ROUTE */
    Route::get('/{any}', [SpaController::class, 'index'])->where('any', '^(?!api\/)[\/\w\.-]*');
});
