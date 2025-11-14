<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaidEvent>
 */
class PaidEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);
        $startTime = fake()->dateTimeBetween('now', '+1 month');
        $deadline = fake()->dateTimeBetween($startTime, '+2 months');

        return [
            'title' => ucfirst($title),
            'slug' => \Illuminate\Support\Str::slug($title),
            'semester' => fake()->randomElement(['Spring 2024', 'Fall 2024', 'Summer 2024', 'Spring 2025', 'Fall 2025']),
            'description' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'registration_start_time' => $startTime,
            'registration_deadline' => $deadline,
            'registration_limit' => fake()->optional(0.7)->numberBetween(20, 100),
            'status' => fake()->randomElement(['draft', 'published', 'closed']),
        ];
    }
}
