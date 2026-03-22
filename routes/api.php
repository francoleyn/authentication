<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Public routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/popular', [CategoryController::class, 'popular']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Public file access (for public files)
Route::get('/files/{file}/download', [FileController::class, 'download'])->name('api.files.download');
Route::get('/files/{file}/stream', [FileController::class, 'stream'])->name('api.files.stream');

Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/users', [RoleController::class, 'users'])->middleware('permission:view users');
    
    // Roles & Permissions
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'roles'])->middleware('permission:view roles');
        Route::post('/assign', [RoleController::class, 'assignRole'])->middleware('role:admin');
        Route::post('/remove', [RoleController::class, 'removeRole'])->middleware('role:admin');
    });
    Route::get('/permissions', [RoleController::class, 'permissions'])->middleware('role:admin');

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::post('/send', [NotificationController::class, 'send'])->middleware('role:admin');
        Route::post('/send-all', [NotificationController::class, 'sendToAll'])->middleware('role:admin');
    });

    // Profile (One-to-One relationship)
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });

    // Posts (One-to-Many relationship)
    Route::prefix('posts')->group(function () {
        Route::get('/my-posts', [PostController::class, 'myPosts']);
        Route::post('/', [PostController::class, 'store']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
    });

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Admin: Comment moderation
    Route::middleware('role:admin')->group(function () {
        Route::get('/comments/pending', [CommentController::class, 'pending']);
        Route::post('/comments/{comment}/approve', [CommentController::class, 'approve']);
    });

    // Admin: Category management
    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // File Management
    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index']);
        Route::get('/stats', [FileController::class, 'myFiles']);
        Route::post('/upload', [FileController::class, 'store']);
        Route::post('/upload-multiple', [FileController::class, 'storeMultiple']);
        Route::get('/{file}', [FileController::class, 'show']);
        Route::put('/{file}', [FileController::class, 'update']);
        Route::delete('/{file}', [FileController::class, 'destroy']);
        Route::post('/delete-multiple', [FileController::class, 'destroyMultiple']);
    });
});
