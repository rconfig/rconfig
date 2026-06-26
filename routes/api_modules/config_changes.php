<?php

use Illuminate\Support\Facades\Route;

Route::prefix('config-changes')->group(function () {
    // Diff record for a given current config id (per-config changes panel).
    Route::get('current-config/{id}', 'ConfigChangesController@showByCurrentConfigId');
});
