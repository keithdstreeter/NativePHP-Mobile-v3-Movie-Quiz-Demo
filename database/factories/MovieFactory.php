<?php

namespace Database\Factories;

use App\Models\AgeGroup;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'age_group_id' => AgeGroup::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(3),
            'release_year' => fake()->numberBetween(1990, 2025),
            'poster_path' => null,
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 10),
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
