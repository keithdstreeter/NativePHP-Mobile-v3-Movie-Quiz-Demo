<?php

namespace Database\Factories;

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizSession>
 */
class QuizSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'movie_id' => Movie::factory(),
            'age_group_id' => AgeGroup::factory(),
            'question_count' => 10,
            'correct_count' => 0,
            'started_at' => now(),
            'completed_at' => null,
            'duration_seconds' => null,
            'question_ids' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
            'correct_count' => fake()->numberBetween(0, $attributes['question_count'] ?? 10),
            'duration_seconds' => fake()->numberBetween(30, 300),
        ]);
    }
}
