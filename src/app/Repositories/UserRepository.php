<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Проверить существует ли пользователь с данным email
     */
    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

}

