<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::prefix("v1")->group(function () {
    // 1. Rute Publik (Tanpa Token)
    Route::prefix("auth")->group(function () {
        Route::post("/register", [AuthController::class, "register"]);
    });

    // 2. Rute Terproteksi (Wajib membawa Token JWT)
    Route::middleware("auth:api")->group(function () {
        Route::prefix("auth")->group(function () {
            Route::post("/onboarding", [AuthController::class, "onboarding"]);
        });

        // Rute fitur lain
    });
});
