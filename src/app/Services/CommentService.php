<?php

namespace App\Services;

use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\TaskRepository;
use App\StateMachines\CommentStatusStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CommentService
{
    protected CommentRepository $commentRepository;
    protected TaskRepository $taskRepository;
    protected CommentStatusStateMachine $stateMachine;

    public function __construct(CommentRepository $commentRepository, TaskRepository $taskRepository, CommentStatusStateMachine $stateMachine)
    {
        $this->commentRepository = $commentRepository;
        $this->taskRepository = $taskRepository;
        $this->stateMachine = $stateMachine;
    }

    /**
     * Получить комментарии к задаче
     */
    public function getComments(int $taskId, int $userId): Collection
    {
        // Проверяем доступ к задаче
        if (!$this->taskRepository->isUserParticipant($taskId, $userId)) {
            throw ValidationException::withMessages([
                'task' => ['Задача не найдена или у вас нет доступа к ней.'],
            ]);
        }

        return $this->commentRepository->getCommentsByTaskId($taskId);
    }

    /**
     * Создать комментарий к задаче
     */
    public function createComment(array $data, int $taskId, int $userId): Comment
    {
        // Проверяем доступ к задаче
        if (!$this->taskRepository->isUserParticipant($taskId, $userId)) {
            throw ValidationException::withMessages([
                'task' => ['Задача не найдена или у вас нет доступа к ней.'],
            ]);
        }

        // Формируем данные для создания комментария
        $data['task_id'] = $taskId;
        $data['user_id'] = $userId;

        // Устанавливаем статус по умолчанию, если не указан
        if (!isset($data['status'])) {
            $data['status'] = CommentStatusStateMachine::STATUS_VISIBLE;
        }

        // Создаем комментарий через репозиторий
        $comment = $this->commentRepository->create($data);
        $comment->load('user');

        return $comment;
    }

    /**
     * Получить комментарий по ID
     */
    public function getComment(int $commentId, int $userId): Comment
    {
        $comment = $this->commentRepository->findOrFail($commentId);

        // Проверяем доступ к задаче, к которой относится комментарий
        if (!$this->taskRepository->isUserParticipant($comment->task_id, $userId)) {
            throw ValidationException::withMessages([
                'comment' => ['Комментарий не найден или у вас нет доступа к нему.'],
            ]);
        }

        $comment->load('user');
        return $comment;
    }

    /**
     * Обновить комментарий
     */
    public function updateComment(int $commentId, array $data, int $userId): Comment
    {
        $comment = $this->commentRepository->findOrFail($commentId);

        // Проверяем, что комментарий принадлежит пользователю
        if ($comment->user_id !== $userId) {
            throw ValidationException::withMessages([
                'comment' => ['Вы можете редактировать только свои комментарии.'],
            ]);
        }

        // Если изменяется статус, проверяем через state machine
        if (isset($data['status']) && $data['status'] !== $comment->status) {
            if (!$this->stateMachine->canTransition($comment->status, $data['status'])) {
                throw ValidationException::withMessages([
                    'status' => [
                        "Недопустимый переход статуса: из '{$comment->status}' в '{$data['status']}'"
                    ],
                ]);
            }
        }

        $this->commentRepository->update($comment, $data);
        $comment->load('user');

        return $comment->fresh();
    }

    /**
     * Скрыть комментарий (вместо физического удаления)
     */
    public function deleteComment(int $commentId, int $userId): void
    {
        $comment = $this->commentRepository->findOrFail($commentId);

        // Проверяем, что комментарий принадлежит пользователю
        if ($comment->user_id !== $userId) {
            throw ValidationException::withMessages([
                'comment' => ['Вы можете скрывать только свои комментарии.'],
            ]);
        }

        // Используем state machine для скрытия комментария
        if ($comment->status !== CommentStatusStateMachine::STATUS_HIDDEN) {
            if (!$this->stateMachine->canTransition($comment->status, CommentStatusStateMachine::STATUS_HIDDEN)) {
                throw ValidationException::withMessages([
                    'status' => ['Недопустимый переход статуса комментария.'],
                ]);
            }

            $this->commentRepository->update($comment, ['status' => CommentStatusStateMachine::STATUS_HIDDEN]);
        }
    }
}

