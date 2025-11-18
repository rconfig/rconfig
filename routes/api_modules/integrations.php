<?php

use Illuminate\Support\Facades\Route;

Route::prefix('integrations')->group(function () {
    Route::resource('/options', 'IntegrationOptionController')->only(['index']);
    Route::resource('/configured', 'IntegrationConfiguredController')->only(['index', 'show', 'destroy']);
});