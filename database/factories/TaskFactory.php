<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

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
            'project_id' => Project::inRandomOrder()->first()?->id ?? Project::factory(),
            'assigned_to' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'title' => fake()->sentence(4),
            'status' => fake()->randomElement(['pending', 'in-progress', 'completed']),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
