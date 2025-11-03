<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Иван Иванов',
                'email' => 'ivan@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Мария Петрова',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Петр Сидоров',
                'email' => 'petr@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Анна Козлова',
                'email' => 'anna@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Сергей Волков',
                'email' => 'sergey@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}

