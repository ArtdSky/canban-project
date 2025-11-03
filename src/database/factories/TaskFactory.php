<?php

namespace Database\Factories;

use App\Models\User;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'status' => TaskStatusStateMachine::STATUS_TODO,
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}

