<?php

namespace Database\Factories;

use App\StateMachines\CommentStatusStateMachine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => \App\Models\Task::factory(),
            'user_id' => \App\Models\User::factory(),
            'content' => fake()->paragraph(1),
            'status' => CommentStatusStateMachine::STATUS_VISIBLE,
        ];
    }
}

