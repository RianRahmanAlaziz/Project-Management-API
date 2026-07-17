<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\Api\V1\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)
    ->name('health');

// Authentication

Route::prefix('auth')->name('auth.')->group(function (): void {

    // Endpoint untuk guest.
    Route::middleware('throttle:5,1')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Endpoint yang membutuhkan Bearer token.
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [ProfileController::class, 'show'])->name('me');

        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
});

Route::middleware('auth:sanctum')->group(function (): void {

    // Workspace
    Route::apiResource('workspaces', WorkspaceController::class);
    // Workspace Members
    Route::apiResource('workspaces.members', WorkspaceMemberController::class);
});
