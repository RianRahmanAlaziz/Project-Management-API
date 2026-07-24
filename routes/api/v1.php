<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\KanbanColumnController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ProjectMemberController;
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
    Route::patch('workspaces/{workspace}/transfer-ownership',  [WorkspaceController::class, 'transferOwnership'])->name('workspaces.transfer-ownership');

    Route::apiResource('workspaces', WorkspaceController::class);

    // Workspace Members
    Route::get('workspaces/{workspace}/members/available', [WorkspaceMemberController::class, 'availableMembers'])->name('v1.workspaces.members.available');

    Route::apiResource('workspaces.members', WorkspaceMemberController::class)->only([
        'index',
        'store',
        'update',
        'destroy',
    ]);

    // Projects
    Route::apiResource('workspaces.projects', ProjectController::class);
    // Projects Members
    Route::apiResource('workspaces.projects.members', ProjectMemberController::class);

    Route::prefix('workspaces/{workspace}/projects/{project}/columns')->group(function () {

        Route::get('/', [KanbanColumnController::class, 'index'])->name('v1.projects.columns.index');

        Route::post('/', [KanbanColumnController::class, 'store'])->name('v1.projects.columns.store');

        /*
         * Harus sebelum /{column} supaya "reorder"
         * tidak dianggap sebagai parameter column.
        */
        Route::patch('/reorder', [KanbanColumnController::class, 'reorder'])->name('v1.projects.columns.reorder');

        Route::patch('/{column}', [KanbanColumnController::class, 'update'])->name('v1.projects.columns.update');

        Route::delete('/{column}', [KanbanColumnController::class, 'destroy'])->name('v1.projects.columns.destroy');
    });
});
