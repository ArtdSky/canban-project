<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class TaskService
{
    protected TaskRepository $taskRepository;
    protected TaskStatusStateMachine $stateMachine;

    public function __construct(TaskRepository $taskRepository, TaskStatusStateMachine $stateMachine)
    {
        $this->taskRepository = $taskRepository;
        $this->stateMachine = $stateMachine;
    }

    /**
     * Получить список задач пользователя с фильтрацией по статусу
     */
    public function getTasks(int $userId, ?string $status = null): Collection
    {
        if ($status) {
            // Валидация статуса через state machine
            if (!$this->stateMachine->isValidStatus($status)) {
                throw ValidationException::withMessages([
                    'status' => ['Недопустимый статус задачи.'],
                ]);
            }
            return $this->taskRepository->getTasksByStatus($status, $userId);
        }

        return $this->taskRepository->getTasksByUserId($userId);
    }

    /**
     * Получить задачу по ID (с проверкой доступа)
     */
    public function getTask(int $taskId, int $userId): Task
    {
        $task = $this->taskRepository->findTaskForUser($taskId, $userId);

        if (!$task) {
            throw ValidationException::withMessages([
                'task' => ['Задача не найдена или у вас нет доступа к ней.'],
            ]);
        }

        return $task;
    }

    /**
     * Создать новую задачу
     */
    public function createTask(array $data, int $creatorId): Task
    {
        // Устанавливаем статус по умолчанию, если не указан
        if (!isset($data['status'])) {
            $data['status'] = TaskStatusStateMachine::STATUS_TODO;
        }

        // Извлекаем массивы участников перед созданием задачи
        $assigneeIds = $data['assignee_ids'] ?? [];
        $observerIds = $data['observer_ids'] ?? [];

        // Удаляем их из массива данных для создания задачи
        unset($data['assignee_ids'], $data['observer_ids']);

        // Создаем задачу через репозиторий
        $task = $this->taskRepository->create($data);

        // Автоматически добавляем создателя в участники с ролью creator
        $task->participants()->create([
            'user_id' => $creatorId,
            'role' => 'creator',
        ]);

        // Добавляем исполнителей
        if (!empty($assigneeIds)) {
            foreach ($assigneeIds as $assigneeId) {
                // Проверяем, нет ли уже такой роли у этого пользователя
                $exists = $task->participants()
                    ->where('user_id', $assigneeId)
                    ->where('role', 'assignee')
                    ->exists();

                if (!$exists) {
                    $task->participants()->create([
                        'user_id' => $assigneeId,
                        'role' => 'assignee',
                    ]);
                }
            }
        }

        // Добавляем наблюдателей
        if (!empty($observerIds)) {
            foreach ($observerIds as $observerId) {
                // Проверяем, нет ли уже такой роли у этого пользователя
                $exists = $task->participants()
                    ->where('user_id', $observerId)
                    ->where('role', 'observer')
                    ->exists();

                if (!$exists) {
                    $task->participants()->create([
                        'user_id' => $observerId,
                        'role' => 'observer',
                    ]);
                }
            }
        }

        // Загружаем связи
        $task->load(['participants.user']);

        return $task;
    }

    /**
     * Обновить задачу
     */
    public function updateTask(int $taskId, array $data, int $userId): Task
    {
        $task = $this->getTask($taskId, $userId);

        // Если изменяется статус, проверяем через state machine
        if (isset($data['status']) && $data['status'] !== $task->status) {
            if (!$this->stateMachine->canTransition($task->status, $data['status'])) {
                throw ValidationException::withMessages([
                    'status' => [
                        "Недопустимый переход статуса: из '{$task->status}' в '{$data['status']}'"
                    ],
                ]);
            }
        }

        // Извлекаем массивы участников перед обновлением задачи
        $assigneeIds = $data['assignee_ids'] ?? null;
        $observerIds = $data['observer_ids'] ?? null;

        // Удаляем их из массива данных для обновления задачи
        unset($data['assignee_ids'], $data['observer_ids']);

        // Обновляем задачу через репозиторий
        $this->taskRepository->update($task, $data);

        // Если переданы исполнители, обновляем список
        if ($assigneeIds !== null) {
            // Удаляем исполнителей, которых нет в новом списке (не трогаем других ролей)
            $task->participants()
                ->where('role', 'assignee')
                ->whereNotIn('user_id', $assigneeIds)
                ->delete();

            // Добавляем новых исполнителей
            foreach ($assigneeIds as $assigneeId) {
                // Проверяем, нет ли уже такой роли у этого пользователя
                $exists = $task->participants()
                    ->where('user_id', $assigneeId)
                    ->where('role', 'assignee')
                    ->exists();

                if (!$exists) {
                    $task->participants()->create([
                        'user_id' => $assigneeId,
                        'role' => 'assignee',
                    ]);
                }
            }
        }

        // Если переданы наблюдатели, обновляем список
        if ($observerIds !== null) {
            // Удаляем наблюдателей, которых нет в новом списке (не трогаем других ролей)
            $task->participants()
                ->where('role', 'observer')
                ->whereNotIn('user_id', $observerIds)
                ->delete();

            // Добавляем новых наблюдателей
            foreach ($observerIds as $observerId) {
                // Проверяем, нет ли уже такой роли у этого пользователя
                $exists = $task->participants()
                    ->where('user_id', $observerId)
                    ->where('role', 'observer')
                    ->exists();

                if (!$exists) {
                    $task->participants()->create([
                        'user_id' => $observerId,
                        'role' => 'observer',
                    ]);
                }
            }
        }

        // Загружаем связи
        $task->load(['participants.user']);

        return $task->fresh();
    }

    /**
     * Удалить задачу
     */
    public function deleteTask(int $taskId, int $userId): void
    {
        $task = $this->getTask($taskId, $userId);
        $this->taskRepository->delete($task);
    }

    /**
     * Добавить участника к задаче
     */
    public function addParticipant(int $taskId, int $userId, int $participantUserId, string $role): void
    {
        $task = $this->getTask($taskId, $userId);

        // Валидация роли
        $allowedRoles = ['creator', 'assignee', 'observer'];
        if (!in_array($role, $allowedRoles, true)) {
            throw ValidationException::withMessages([
                'role' => ['Недопустимая роль. Разрешены: ' . implode(', ', $allowedRoles)],
            ]);
        }

        // Проверяем, нет ли уже такой роли у этого пользователя
        $exists = $task->participants()
            ->where('user_id', $participantUserId)
            ->where('role', $role)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'user_id' => ['Пользователь уже имеет эту роль в задаче.'],
            ]);
        }

        $task->participants()->create([
            'user_id' => $participantUserId,
            'role' => $role,
        ]);
    }

    /**
     * Удалить участника из задачи (или конкретную роль)
     */
    public function removeParticipant(int $taskId, int $userId, int $participantUserId, ?string $role = null): void
    {
        $task = $this->getTask($taskId, $userId);

        // Получаем ID создателя
        $creatorId = $task->getCreatorIdAttribute();

        // Нельзя удалить роль creator (создателя)
        if ($role === 'creator' || ($role === null && $creatorId === $participantUserId)) {
            throw ValidationException::withMessages([
                'user_id' => ['Нельзя удалить роль создателя задачи.'],
            ]);
        }

        // Если указана конкретная роль, удаляем только её
        if ($role !== null) {
            $task->participants()
                ->where('user_id', $participantUserId)
                ->where('role', $role)
                ->delete();
        } else {
            // Удаляем все роли кроме creator
            $task->participants()
                ->where('user_id', $participantUserId)
                ->where('role', '!=', 'creator')
                ->delete();
        }
    }
}

