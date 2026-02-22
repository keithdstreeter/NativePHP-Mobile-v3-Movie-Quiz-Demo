<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'movie_id' => Movie::factory(),
            'prompt' => fake()->sentence().'?',
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'kind' => 'multiple_choice',
            'explanation' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
