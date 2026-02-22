<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->seedMovies();
        $this->seedQuestions();
    }

    public function down(): void
    {
        $slugs = collect($this->loadJson('movies_extra.json'))->pluck('slug');

        Movie::query()->whereIn('slug', $slugs)->each(function (Movie $movie) {
            $movie->questions()->each(function (Question $question) {
                $question->choices()->delete();
            });
            $movie->questions()->delete();
            $movie->delete();
        });
    }

    private function seedMovies(): void
    {
        $movies = $this->loadJson('movies_extra.json');

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

    private function seedQuestions(): void
    {
        $dataPath = database_path('data');
        $slugs = collect($this->loadJson('movies_extra.json'))->pluck('slug');

        foreach ($slugs as $slug) {
            $file = $dataPath.'/questions_'.$slug.'.json';

            if (! file_exists($file)) {
                continue;
            }

            $movie = Movie::query()->where('slug', $slug)->firstOrFail();
            $questions = json_decode(file_get_contents($file), true);

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
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function loadJson(string $filename): array
    {
        $path = database_path('data/'.$filename);

        return json_decode(file_get_contents($path), true);
    }
};
