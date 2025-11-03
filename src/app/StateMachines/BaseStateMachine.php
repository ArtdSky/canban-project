<?php

namespace App\StateMachines;

/**
 * Базовый абстрактный класс для конечных автоматов
 * Использует обычные методы вместо статических для лучшей тестируемости и DI
 */
abstract class BaseStateMachine
{
    /**
     * Получить все доступные статусы
     * Должен быть реализован в дочерних классах
     *
     * @return array<string>
     */
    abstract public function getAllStatuses(): array;

    /**
     * Получить матрицу допустимых переходов
     * [текущий_статус => [разрешенные_статусы]]
     * Должен быть реализован в дочерних классах
     *
     * @return array<string, array<string>>
     */
    abstract protected function getAllowedTransitions(): array;

    /**
     * Проверить, является ли статус валидным
     */
    public function isValidStatus(string $status): bool
    {
        return in_array($status, $this->getAllStatuses(), true);
    }

    /**
     * Проверить, допустим ли переход между статусами
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if (!$this->isValidStatus($fromStatus) || !$this->isValidStatus($toStatus)) {
            return false;
        }

        // Если статус не изменился
        if ($fromStatus === $toStatus) {
            return true;
        }

        // Получаем матрицу переходов
        $allowedTransitions = $this->getAllowedTransitions();

        // Проверяем матрицу переходов
        return isset($allowedTransitions[$fromStatus]) &&
               in_array($toStatus, $allowedTransitions[$fromStatus], true);
    }

    /**
     * Получить список допустимых переходов из текущего статуса
     */
    public function getAvailableTransitions(string $currentStatus): array
    {
        if (!$this->isValidStatus($currentStatus)) {
            return [];
        }

        $allowedTransitions = $this->getAllowedTransitions();
        return $allowedTransitions[$currentStatus] ?? [];
    }

    /**
     * Выполнить переход статуса (с проверкой)
     *
     * @param string $fromStatus
     * @param string $toStatus
     * @return string
     * @throws \InvalidArgumentException если переход недопустим
     */
    public function transition(string $fromStatus, string $toStatus): string
    {
        if (!$this->canTransition($fromStatus, $toStatus)) {
            $entityName = $this->getEntityName();
            throw new \InvalidArgumentException(
                "Недопустимый переход статуса {$entityName}: из '{$fromStatus}' в '{$toStatus}'"
            );
        }

        return $toStatus;
    }

    /**
     * Получить название сущности для сообщений об ошибках
     * Используется только внутри класса
     *
     * @return string
     */
    private function getEntityName(): string
    {
        // Используем имя класса без суффикса StateMachine
        $className = class_basename(static::class);
        return str_replace('StateMachine', '', $className);
    }
}
