<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\StateMachines\CommentStatusStateMachine;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем пользователя 2 (Мария Петрова)
        $user2 = User::where('email', 'maria@example.com')->first();

        if (!$user2) {
            $this->command->error('Пользователь 2 не найден. Сначала запустите UserSeeder.');
            return;
        }

        // Получаем первую задачу (созданную в TaskSeeder)
        $task = Task::first();

        if (!$task) {
            $this->command->error('Задача не найдена. Сначала запустите TaskSeeder.');
            return;
        }

        // Проверяем, что пользователь 2 имеет доступ к задаче (является участником)
        $hasAccess = $task->participants()
            ->where('user_id', $user2->id)
            ->exists();

        if (!$hasAccess) {
            $this->command->error('Пользователь 2 не имеет доступа к задаче.');
            return;
        }

        // Создаем комментарий от пользователя 2 к задаче
        Comment::create([
            'task_id' => $task->id,
            'user_id' => $user2->id,
            'content' => 'сделайте предварительную оценку',
            'status' => CommentStatusStateMachine::STATUS_VISIBLE,
        ]);

        $this->command->info('Комментарий создан успешно!');
    }
}

