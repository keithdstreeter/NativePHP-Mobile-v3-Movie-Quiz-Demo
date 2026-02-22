<?php

namespace Database\Factories;

use App\Models\PendingSync;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PendingSync>
 */
class PendingSyncFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'endpoint' => '/api/scores',
            'method' => 'POST',
            'payload' => [
                'device_id' => fake()->uuid(),
                'username' => 'User'.strtoupper(fake()->lexify('??????')),
                'movie_slug' => fake()->slug(3),
                'score' => fake()->numberBetween(0, 10),
                'total' => 10,
                'played_at' => now()->toISOString(),
            ],
        ];
    }
}
