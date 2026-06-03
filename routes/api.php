<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 1. Rute Publik (Tanpa Token)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Rute Broadcasting untuk API JWT
    Broadcast::routes(['middleware' => ['auth:api']]);

    // 2. Rute Terproteksi (Wajib membawa Token JWT)
    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/onboarding', [AuthController::class, 'onboarding']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        Route::prefix('chat')->group(function () {
            Route::get('/rooms', [MessageController::class, 'getRooms']);
            Route::get('/rooms/{roomId}/messages', [
                MessageController::class,
                'getMessages',
            ]);
            Route::post('/messages', [MessageController::class, 'sendMessage']);
        });

        Route::get('/users/search', [UserController::class, 'search']);

        Route::prefix('contacts')->group(function () {
            Route::post('/request', [ContactController::class, 'sendRequest']);
            Route::get('/requests/pending', [
                ContactController::class,
                'pendingRequests',
            ]);
            Route::patch('/requests/{id}/respond', [
                ContactController::class,
                'respondRequest',
            ]);
        });
        // Rute fitur lain
    });
});
