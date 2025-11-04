<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\TaskController;
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
    
    // Пользователи (для выбора в формах)
    Route::get('/users', [UserController::class, 'index']);

    // Задачи (CRUD)
    Route::apiResource('tasks', TaskController::class);

    // Комментарии к задачам
    Route::prefix('tasks/{task}/comments')->group(function () {
        Route::get('/', [CommentController::class, 'index']);        // Список комментариев задачи
        Route::post('/', [CommentController::class, 'store']);      // Создание комментария
    });

    // Комментарии (CRUD для отдельных комментариев)
    Route::prefix('comments')->group(function () {
        Route::get('/{comment}', [CommentController::class, 'show']);     // Просмотр комментария
        Route::put('/{comment}', [CommentController::class, 'update']);    // Обновление комментария
        Route::delete('/{comment}', [CommentController::class, 'destroy']); // Удаление комментария
    });
});

