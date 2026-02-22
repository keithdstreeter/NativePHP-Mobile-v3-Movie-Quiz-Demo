<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Seed the movies table from JSON data.
     */
    public function run(): void
    {
        $path = database_path('data/movies.json');
        $movies = json_decode(file_get_contents($path), true);

        foreach ($movies as $movieData) {
            $ageGroup = AgeGroup::query()
                ->where('code', $movieData['age_group_code'])
                ->firstOrFail();

            $attributes = collect($movieData)
                ->except('age_group_code')
                ->put('age_group_id', $ageGroup->id)
                ->all();

            Movie::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }
}
