<?php

use App\Support\Api\ApiResponse;
use App\Http\Controllers\Api\V1\DeveloperPlatformController;
use App\Http\Controllers\Internal\V1\InternalApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ApiResponse::success([
    'service' => 'ivp',
    'status' => 'ok',
]));

Route::prefix('v1')->group(function (): void {
    Route::get('/developer-platform', [DeveloperPlatformController::class, 'index']);
});

Route::prefix('internal/v1')->group(function (): void {
    Route::get('/health', [InternalApiController::class, 'health']);
});
