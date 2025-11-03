<?php

namespace App\StateMachines;

/**
 * Конечный автомат для управления статусами задач
 */
class TaskStatusStateMachine extends BaseStateMachine
{
    /**
     * Доступные статусы задач
     */
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    /**
     * Матрица допустимых переходов статусов
     * [текущий_статус => [разрешенные_статусы]]
     */
    private const ALLOWED_TRANSITIONS = [
        self::STATUS_TODO => [self::STATUS_IN_PROGRESS, self::STATUS_DONE],
        self::STATUS_IN_PROGRESS => [self::STATUS_TODO, self::STATUS_DONE],
        self::STATUS_DONE => [self::STATUS_IN_PROGRESS], // Из done можно вернуться в работу
    ];

    /**
     * Все доступные статусы
     */
    public function getAllStatuses(): array
    {
        return [
            self::STATUS_TODO,
            self::STATUS_IN_PROGRESS,
            self::STATUS_DONE,
        ];
    }

    /**
     * Получить матрицу допустимых переходов
     *
     * @return array<string, array<string>>
     */
    protected function getAllowedTransitions(): array
    {
        return self::ALLOWED_TRANSITIONS;
    }
}
