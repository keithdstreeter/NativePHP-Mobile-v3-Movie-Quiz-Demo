<?php

namespace Database\Factories;

use App\Models\LeaderboardEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LeaderboardEntry>
 */
class LeaderboardEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Str::uuid()->toString(),
            'username' => 'User'.strtoupper(fake()->lexify('??????')),
            'movie_slug' => fake()->slug(3),
            'score' => fake()->numberBetween(0, 10),
            'total' => 10,
            'played_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }
}
