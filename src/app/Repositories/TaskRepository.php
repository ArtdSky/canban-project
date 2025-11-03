<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository extends BaseRepository
{
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Получить задачи пользователя (где он является участником)
     */
    public function getTasksByUserId(int $userId, ?string $status = null): Collection
    {
        $query = $this->model->whereHas('participants', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['participants.user'])->get();
    }

    /**
     * Найти задачу по ID, если пользователь является участником
     */
    public function findTaskForUser(int $taskId, int $userId): ?Task
    {
        return $this->model->where('id', $taskId)
            ->whereHas('participants', function (Builder $q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['participants.user', 'comments.user'])
            ->first();
    }

    /**
     * Проверить, является ли пользователь участником задачи
     */
    public function isUserParticipant(int $taskId, int $userId): bool
    {
        return $this->model->where('id', $taskId)
            ->whereHas('participants', function (Builder $q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->exists();
    }

    /**
     * Получить задачи с фильтрацией по статусу
     */
    public function getTasksByStatus(string $status, int $userId): Collection
    {
        return $this->model->where('status', $status)
            ->whereHas('participants', function (Builder $q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['participants.user'])
            ->get();
    }

    /**
     * Создать задачу
     */
    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    /**
     * Обновить задачу
     */
    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    /**
     * Удалить задачу
     */
    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}

