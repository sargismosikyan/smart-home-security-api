<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceActivityController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SecurityAlertController;
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

    Route::get('/devices', [DeviceController::class, 'index']);
    Route::get('/devices/{device}', [DeviceController::class, 'show']);
    Route::post('/devices', [DeviceController::class, 'store']);
    Route::patch('/devices/{device}', [DeviceController::class, 'update']);

    Route::post('/device-activities', [DeviceActivityController::class, 'store']);

    Route::get('/security-alerts', [SecurityAlertController::class, 'index']);
    Route::get('/security-alerts/{securityAlert}', [SecurityAlertController::class, 'show']);
    Route::patch('/security-alerts/{securityAlert}', [SecurityAlertController::class, 'update']);
});
