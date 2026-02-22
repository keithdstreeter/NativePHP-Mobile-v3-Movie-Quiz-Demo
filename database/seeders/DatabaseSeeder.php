<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Data seeding is handled by migrations for NativePHP compatibility.
     * These seeders are kept for manual re-seeding if needed.
     */
    public function run(): void
    {
        $this->call([
            AgeGroupSeeder::class,
            MovieSeeder::class,
            QuestionSeeder::class,
        ]);
    }
}
