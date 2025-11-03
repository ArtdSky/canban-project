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

        if (!$user1 || !$user2 || !$user3 || !$user4) {
            $this->command->error('Не все пользователи найдены. Сначала запустите UserSeeder.');
            return;
        }

        // Создаем задачу
        $task = Task::create([
            'title' => 'Разработать систему управления задачами',
            'description' => 'Необходимо разработать систему для управления задачами с возможностью назначения исполнителей и наблюдателей.',
            'status' => TaskStatusStateMachine::STATUS_IN_PROGRESS,
            'due_date' => now()->addDays(30),
        ]);

        // Добавляем участников:
        // 1 пользователь - создатель (creator)
        $task->participants()->create([
            'user_id' => $user1->id,
            'role' => 'creator',
        ]);

        // 2 и 3 пользователи - наблюдатели (observer)
        $task->participants()->create([
            'user_id' => $user2->id,
            'role' => 'observer',
        ]);

        $task->participants()->create([
            'user_id' => $user3->id,
            'role' => 'observer',
        ]);

        // 4 пользователь - исполнитель (assignee)
        $task->participants()->create([
            'user_id' => $user4->id,
            'role' => 'assignee',
        ]);

        $this->command->info('Задача создана успешно!');
    }
}

