<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [RoleController::class, 'users'])->middleware('permission:view users');
    
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'roles'])->middleware('permission:view roles');
        Route::post('/assign', [RoleController::class, 'assignRole'])->middleware('role:admin');
        Route::post('/remove', [RoleController::class, 'removeRole'])->middleware('role:admin');
    });

    Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('role:admin');

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        
        // Admin only - send notifications
        Route::post('/send', [NotificationController::class, 'send'])->middleware('role:admin');
        Route::post('/send-all', [NotificationController::class, 'sendToAll'])->middleware('role:admin');
    });
});
