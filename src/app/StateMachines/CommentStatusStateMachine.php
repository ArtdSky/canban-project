<?php

namespace App\StateMachines;

/**
 * Конечный автомат для управления статусами комментариев
 */
class CommentStatusStateMachine extends BaseStateMachine
{
    /**
     * Доступные статусы комментариев
     */
    public const STATUS_VISIBLE = 'visible';
    public const STATUS_HIDDEN = 'hidden';

    /**
     * Матрица допустимых переходов статусов
     * [текущий_статус => [разрешенные_статусы]]
     */
    private const ALLOWED_TRANSITIONS = [
        self::STATUS_VISIBLE => [self::STATUS_HIDDEN],
        self::STATUS_HIDDEN => [self::STATUS_VISIBLE], // Можно восстановить скрытый комментарий
    ];

    /**
     * Все доступные статусы
     */
    public function getAllStatuses(): array
    {
        return [
            self::STATUS_VISIBLE,
            self::STATUS_HIDDEN,
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
