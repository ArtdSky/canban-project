<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Пользователь может просматривать задачу только если он участник
     */
    public function view(User $user, Task $task): bool
    {
        return $task->hasParticipant($user->id);
    }

    /**
     * Пользователь может создавать задачи (все авторизованные)
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Пользователь может обновлять задачу только если он участник
     */
    public function update(User $user, Task $task): bool
    {
        return $task->hasParticipant($user->id);
    }

    /**
     * Пользователь может удалять задачу только если он создатель
     */
    public function delete(User $user, Task $task): bool
    {
        $creatorId = $task->getCreatorIdAttribute();
        return $creatorId === $user->id;
    }
}
