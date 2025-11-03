<?php

namespace App\Repositories;

use App\Models\Comment;

class CommentRepository extends BaseRepository
{
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }

    /**
     * Получить комментарии к задаче (только видимые)
     */
    public function getCommentsByTaskId(int $taskId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('task_id', $taskId)
            ->where('status', 'visible') // Показываем только видимые комментарии
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Проверить, принадлежит ли комментарий пользователю
     */
    public function isCommentOwner(int $commentId, int $userId): bool
    {
        return $this->model->where('id', $commentId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Создать комментарий
     */
    public function create(array $data): Comment
    {
        return $this->model->create($data);
    }

    /**
     * Найти комментарий по ID
     */
    public function findById(int $id): ?Comment
    {
        return $this->model->find($id);
    }

    /**
     * Найти комментарий по ID или выбросить исключение
     */
    public function findOrFail(int $id): Comment
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Обновить комментарий
     */
    public function update(Comment $comment, array $data): bool
    {
        return $comment->update($data);
    }

    /**
     * Удалить комментарий
     */
    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}

