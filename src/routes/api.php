<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Публичные роуты (без аутентификации)
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

// Защищенные роуты (требуют аутентификацию через Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);

    // Задачи (будут реализованы позже)
    // Route::apiResource('tasks', TaskController::class);
    // Route::apiResource('tasks.comments', CommentController::class);
});

