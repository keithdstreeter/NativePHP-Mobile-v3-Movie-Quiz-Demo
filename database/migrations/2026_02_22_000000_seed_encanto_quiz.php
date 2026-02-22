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

        $this->seedMovie();
        $this->seedQuestions();
    }

    public function down(): void
    {
        $movie = Movie::query()->where('slug', 'encanto')->first();

        if ($movie) {
            $movie->questions()->each(function (Question $question) {
                $question->choices()->delete();
            });
            $movie->questions()->delete();
            $movie->delete();
        }
    }

    private function seedMovie(): void
    {
        $ageGroup = AgeGroup::query()
            ->where('code', '4-6')
            ->firstOrFail();

        Movie::query()->updateOrCreate(
            ['slug' => 'encanto'],
            [
                'age_group_id' => $ageGroup->id,
                'title' => 'Encanto',
                'slug' => 'encanto',
                'release_year' => 2021,
                'poster_path' => 'posters/encanto.jpg',
                'description' => 'The Madrigal family lives in a magical house in the mountains of Colombia, where every child receives a unique magical gift — except Mirabel, who may be the family\'s last hope.',
                'sort_order' => 4,
                'is_active' => true,
            ],
        );
    }

    private function seedQuestions(): void
    {
        $movie = Movie::query()->where('slug', 'encanto')->firstOrFail();
        $questions = json_decode(file_get_contents(database_path('data/questions_encanto.json')), true);

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
};
