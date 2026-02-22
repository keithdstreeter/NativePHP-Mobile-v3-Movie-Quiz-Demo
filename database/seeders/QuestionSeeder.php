<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class QuestionSeeder extends Seeder
{
    /**
     * Seed the questions and question_choices tables from JSON data.
     */
    public function run(): void
    {
        $dataPath = database_path('data');
        $files = File::glob($dataPath.'/questions_*.json');

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
}
