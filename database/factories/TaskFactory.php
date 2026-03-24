<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'worker_reward_per_task' => 1000,
            'is_active' => true,
            'is_approved' => true,
            'quantity' => 100,
            'completed_count' => 0,
            'task_type' => 'micro',
            'platform' => 'twitter',
        ];
    }
}
