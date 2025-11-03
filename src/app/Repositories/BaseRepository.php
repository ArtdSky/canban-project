<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

/**
 * Базовый абстрактный класс для репозиториев
 * Обязывает указывать модель в конструкторе
 */
abstract class BaseRepository
{
    protected Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Получить экземпляр модели для работы с ORM напрямую
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}

