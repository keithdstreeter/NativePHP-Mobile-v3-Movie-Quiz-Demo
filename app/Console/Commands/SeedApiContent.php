<?php

namespace App\Console\Commands;

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use Illuminate\Console\Command;

class SeedApiContent extends Command
{
    protected $signature = 'app:seed-api-content';

    protected $description = 'Seed new quiz content to simulate API-based sync (for demo purposes)';

    public function handle(): void
    {
        $movies = json_decode(file_get_contents(database_path('data/movies_sync.json')), true);

        foreach ($movies as $movieData) {
            $ageGroup = AgeGroup::query()
                ->where('code', $movieData['age_group_code'])
                ->firstOrFail();

            $movie = Movie::query()->updateOrCreate(
                ['slug' => $movieData['slug']],
                [
                    'age_group_id' => $ageGroup->id,
                    'title' => $movieData['title'],
                    'release_year' => $movieData['release_year'],
                    'poster_path' => $movieData['poster_path'],
                    'description' => $movieData['description'],
                    'sort_order' => $movieData['sort_order'],
                    'is_active' => true,
                ],
            );

            $questionsFile = database_path('data/questions_'.$movieData['slug'].'.json');

            if (! file_exists($questionsFile)) {
                $this->warn("No questions file for {$movieData['slug']}, skipping.");

                continue;
            }

            $questions = json_decode(file_get_contents($questionsFile), true);
            $count = 0;

            foreach ($questions as $questionData) {
                $choices = $questionData['choices'];
                unset($questionData['choices']);

                $question = Question::query()->updateOrCreate(
                    [
                        'movie_id' => $movie->id,
                        'prompt' => $questionData['prompt'],
                    ],
                    array_merge($questionData, ['movie_id' => $movie->id]),
                );

                foreach ($choices as $index => $choiceData) {
                    $question->choices()->updateOrCreate(
                        ['label' => $choiceData['label']],
                        array_merge($choiceData, ['sort_order' => $index + 1]),
                    );
                }

                $count++;
            }

            $this->info("Seeded {$movie->title} with {$count} questions.");
        }

        $this->info('API content seeded. The /api/questions endpoint will now serve this data.');
    }
}
