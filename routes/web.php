<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* AUTHENTICATION */

Auth::routes(['register' => false]);
Route::get('/logged-out', [App\Http\Controllers\Auth\LoginController::class, 'showLoggedOut']);

Route::group(['middleware' => 'auth'], function () {

    /* SPA VUE ROUTE */
    Route::get('/{any}', [App\Http\Controllers\SpaController::class, 'index'])->where('any', '^(?!api\/)[\/\w\.-]*');
});
