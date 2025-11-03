<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Регистрация нового пользователя
     *
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function register(array $data): \App\Models\User
    {
        // Проверка на существование email
        if ($this->userRepository->emailExists($data['email'])) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь с таким email уже существует.'],
            ]);
        }

        // Хешируем пароль
        $data['password'] = Hash::make($data['password']);

        // Создаем пользователя через репозиторий
        $user = $this->userRepository->create($data);

        // Возвращаем пользователя без токена (токен выдается только при авторизации)
        return $user;
    }

    /**
     * Авторизация пользователя
     *
     * @param array $credentials
     * @return array ['user' => User, 'token' => string]
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        // Находим пользователя по email
        $user = $this->userRepository->findByEmail($credentials['email']);

        // Проверяем существование пользователя и правильность пароля
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        // Создаем токен Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Выход пользователя (удаление текущего токена)
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function logout($user): void
    {
        // Удаляем текущий токен
        $user->currentAccessToken()->delete();
    }

}

