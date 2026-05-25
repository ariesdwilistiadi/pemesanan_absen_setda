<?php

use App\Http\Controllers\Auth\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [ApiTokenController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/refresh', [ApiTokenController::class, 'refresh'])->middleware('throttle:20,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [ApiTokenController::class, 'me']);
        Route::post('/logout', [ApiTokenController::class, 'logout']);
    });
});
