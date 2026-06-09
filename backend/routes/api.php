<?php

declare(strict_types=1);

use App\Http\Controllers\AuthPortal;
use App\Http\Controllers\DomainPortal;
use App\Http\Controllers\MonitorPortal;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthPortal::class, 'register']);
        Route::post('/login',    [AuthPortal::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthPortal::class, 'logout']);
        Route::get('/me',      [AuthPortal::class, 'me']);

        Route::apiResource('domains', DomainPortal::class);

        Route::get('domains/{domain}/logs',   [MonitorPortal::class, 'index']);
        Route::post('domains/{domain}/check', [MonitorPortal::class, 'triggerCheck'])
            ->middleware('throttle:10,1');
    });
});
