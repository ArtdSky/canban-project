<?php

namespace App\Console\Commands;

use Database\Seeders\CommentSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class SeedAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает все сидеры по очереди: пользователи, задачи, комментарии';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Начинаем заполнение базы данных...');
        $this->newLine();

        $results = [];

        try {
            // 1. Пользователи
            $this->info('📝 Запуск UserSeeder...');
            $seeder = new UserSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            $results[] = ['✅ Выполнен', 'UserSeeder (5 пользователей)'];
            $this->info('✅ Пользователи успешно созданы!');
            $this->newLine();

            // 2. Задачи
            $this->info('📋 Запуск TaskSeeder...');
            $seeder = new TaskSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            $results[] = ['✅ Выполнен', 'TaskSeeder (1 задача с участниками)'];
            $this->info('✅ Задачи успешно созданы!');
            $this->newLine();

            // 3. Комментарии
            $this->info('💬 Запуск CommentSeeder...');
            $seeder = new CommentSeeder();
            $seeder->setCommand($this);
            $seeder->run();
            $results[] = ['✅ Выполнен', 'CommentSeeder (1 комментарий)'];
            $this->info('✅ Комментарии успешно созданы!');
            $this->newLine();

            $this->info('🎉 Все сидеры успешно выполнены!');
            $this->newLine();

            $this->table(
                ['Статус', 'Сидер'],
                $results
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Ошибка при выполнении сидеров: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            $this->newLine();
            return Command::FAILURE;
        }
    }
}

