<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Авторизация пользователя
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'message' => 'Успешная авторизация.',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка авторизации.',
                'errors' => $e->errors(),
            ], 401);
        }
    }

    /**
     * Выход пользователя
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Проверяем наличие пользователя и токена
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Пользователь не авторизован или токен недействителен.',
            ], 401);
        }

        // Проверяем наличие текущего токена перед удалением
        if ($user->currentAccessToken()) {
            $this->authService->logout($user);
        }

        return response()->json([
            'message' => 'Успешный выход из системы.',
        ], 200);
    }
}

