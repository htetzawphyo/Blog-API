<?php

use App\Helper\ResponseHelper;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function() {
    // Profile
    Route::get('profile', [ProfileController::class, 'profile']);
    Route::get('profile-posts', [ProfileController::class, 'posts']);

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Category
    Route::get('categories', [CategoryController::class, 'index']);

    // Post
    Route::get('post', [PostController::class, 'index']);
    Route::post('post/create', [PostController::class, 'create']);
    Route::get('post/{id}', [PostController::class, 'show']);
});