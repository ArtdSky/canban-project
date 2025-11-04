<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\StateMachines\TaskStatusStateMachine;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем пользователей
        $user1 = User::where('email', 'ivan@example.com')->first();
        $user2 = User::where('email', 'maria@example.com')->first();
        $user3 = User::where('email', 'petr@example.com')->first();
        $user4 = User::where('email', 'anna@example.com')->first();
        $user5 = User::where('email', 'sergey@example.com')->first();

        if (!$user1 || !$user2 || !$user3 || !$user4 || !$user5) {
            $this->command->error('Не все пользователи найдены. Сначала запустите UserSeeder.');
            return;
        }

        // Первая задача (оригинальная)
        $taskOriginal = Task::create([
            'title' => 'Разработать систему управления задачами',
            'description' => 'Необходимо разработать систему для управления задачами с возможностью назначения исполнителей и наблюдателей.',
            'status' => TaskStatusStateMachine::STATUS_IN_PROGRESS,
            'due_date' => now()->addDays(30),
        ]);

        // Создатель - user1
        $taskOriginal->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // Наблюдатели - user2 и user3
        $taskOriginal->participants()->create([
            'user_id' => $user2->id,
            'role' => 'observer',
        ]);

        $taskOriginal->participants()->create([
            'user_id' => $user3->id,
            'role' => 'observer',
        ]);

        // Исполнитель - user4
        $taskOriginal->participants()->create([
            'user_id' => $user4->id,
            'role' => 'assignee',
        ]);

        // 1. Задача со статусом "К выполнению" (todo)
        $taskTodo = Task::create([
            'title' => 'Задача к выполнению',
            'description' => 'Задача со статусом "К выполнению". Ожидает начала работы.',
            'status' => TaskStatusStateMachine::STATUS_TODO,
            'due_date' => now()->addDays(7),
        ]);

        // Создатель - user1
        $taskTodo->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // Наблюдатель - user3, Исполнитель - user4
        $taskTodo->participants()->create([
            'user_id' => $user3->id,
            'role' => 'observer',
        ]);

        $taskTodo->participants()->create([
            'user_id' => $user4->id,
            'role' => 'assignee',
        ]);

        // 2. Задача со статусом "В работе" (in_progress)
        $taskInProgress = Task::create([
            'title' => 'Задача в работе',
            'description' => 'Задача со статусом "В работе". Активно выполняется.',
            'status' => TaskStatusStateMachine::STATUS_IN_PROGRESS,
            'due_date' => now()->addDays(5),
        ]);

        // Создатель - user1
        $taskInProgress->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // 3. Задача со статусом "Выполнено" (done)
        $taskDone = Task::create([
            'title' => 'Выполненная задача',
            'description' => 'Задача со статусом "Выполнено". Работа завершена.',
            'status' => TaskStatusStateMachine::STATUS_DONE,
            'due_date' => now()->subDays(1),
        ]);

        // Создатель - user1
        $taskDone->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // Наблюдатель - user2, Исполнитель - user3
        $taskDone->participants()->create([
            'user_id' => $user2->id,
            'role' => 'observer',
        ]);

        $taskDone->participants()->create([
            'user_id' => $user3->id,
            'role' => 'assignee',
        ]);

        // 4. Задача со статусом "Закрыто" (closed)
        $taskClosed = Task::create([
            'title' => 'Завершенная задача',
            'description' => 'Задача со статусом "Закрыто". Окончательно завершена.',
            'status' => TaskStatusStateMachine::STATUS_CLOSED,
            'due_date' => now()->subDays(5),
        ]);

        // Создатель - user1
        $taskClosed->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // Наблюдатель - user4, Исполнитель - user5
        $taskClosed->participants()->create([
            'user_id' => $user4->id,
            'role' => 'observer',
        ]);

        $taskClosed->participants()->create([
            'user_id' => $user5->id,
            'role' => 'assignee',
        ]);

        $this->command->info('Задачи созданы успешно!');
    }
}

