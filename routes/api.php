<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'smart-home-security-api',
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});
