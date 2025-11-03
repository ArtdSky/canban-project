<?php

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
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// Защищенные роуты (требуют аутентификацию через Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Route::apiResource('tasks', TaskController::class);
    // Route::apiResource('tasks.comments', CommentController::class);
});

