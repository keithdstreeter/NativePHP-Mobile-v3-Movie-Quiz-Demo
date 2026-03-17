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

        $this->seedAgeGroups();
        $this->seedMovies();
        $this->seedQuestions();
    }

    public function down(): void
    {
        Question::query()->delete();
        Movie::query()->delete();
        AgeGroup::query()->delete();
    }

    private function seedAgeGroups(): void
    {
        $ageGroups = $this->loadJson('age_groups.json');

        foreach ($ageGroups as $ageGroup) {
            AgeGroup::query()->updateOrCreate(
                ['code' => $ageGroup['code']],
                $ageGroup,
            );
        }
    }

    private function seedMovies(): void
    {
        $movies = $this->loadJson('movies.json');

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
        $files = glob($dataPath.'/questions_*.json');

        foreach ($files as $file) {
            $slug = str_replace(
                ['questions_', '.json'],
                '',
                basename($file),
            );

            $movie = Movie::query()->where('slug', $slug)->first();

            if (! $movie) {
                continue;
            }
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
