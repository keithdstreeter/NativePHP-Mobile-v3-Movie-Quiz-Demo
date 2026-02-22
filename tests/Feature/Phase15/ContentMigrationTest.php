<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;

it('seeds additional movies from extra json without duplicating existing data', function () {
    $ageGroup46 = AgeGroup::factory()->create(['code' => '4-6']);
    $ageGroup79 = AgeGroup::factory()->create(['code' => '7-9']);
    $ageGroup1012 = AgeGroup::factory()->create(['code' => '10-12']);

    $existingMovie = Movie::factory()->create([
        'slug' => 'frozen',
        'age_group_id' => $ageGroup46->id,
    ]);

    $movies = json_decode(file_get_contents(database_path('data/movies_extra.json')), true);

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

    expect(Movie::count())->toBe(4)
        ->and(Movie::where('slug', 'moana')->exists())->toBeTrue()
        ->and(Movie::where('slug', 'spider-man-into-the-spider-verse')->exists())->toBeTrue()
        ->and(Movie::where('slug', 'the-princess-bride')->exists())->toBeTrue()
        ->and(Movie::where('slug', 'frozen')->exists())->toBeTrue();
});

it('seeds questions with valid structure from extra movie json files', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '4-6']);
    $movie = Movie::factory()->create([
        'slug' => 'moana',
        'age_group_id' => $ageGroup->id,
    ]);

    $questions = json_decode(file_get_contents(database_path('data/questions_moana.json')), true);

    foreach ($questions as $questionData) {
        $choices = $questionData['choices'];
        unset($questionData['choices']);

        $question = Question::query()->updateOrCreate(
            ['movie_id' => $movie->id, 'prompt' => $questionData['prompt']],
            array_merge($questionData, ['movie_id' => $movie->id]),
        );

        foreach ($choices as $index => $choiceData) {
            $question->choices()->updateOrCreate(
                ['label' => $choiceData['label']],
                array_merge($choiceData, ['sort_order' => $index + 1]),
            );
        }
    }

    expect($movie->questions()->count())->toBeGreaterThanOrEqual(10);

    $movie->questions->each(function (Question $question) {
        expect($question->choices()->count())->toBe(4)
            ->and($question->choices()->correctAnswer()->count())->toBe(1);
    });
});

it('does not duplicate questions when migration runs twice', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '7-9']);
    $movie = Movie::factory()->create([
        'slug' => 'spider-man-into-the-spider-verse',
        'age_group_id' => $ageGroup->id,
    ]);

    $questions = json_decode(file_get_contents(database_path('data/questions_spider-man-into-the-spider-verse.json')), true);

    $seedQuestions = function () use ($movie, $questions) {
        foreach ($questions as $questionData) {
            $choices = $questionData['choices'];
            unset($questionData['choices']);

            $question = Question::query()->updateOrCreate(
                ['movie_id' => $movie->id, 'prompt' => $questionData['prompt']],
                array_merge($questionData, ['movie_id' => $movie->id]),
            );

            foreach ($choices as $index => $choiceData) {
                $question->choices()->updateOrCreate(
                    ['label' => $choiceData['label']],
                    array_merge($choiceData, ['sort_order' => $index + 1]),
                );
            }
        }
    };

    $seedQuestions();
    $firstCount = $movie->questions()->count();

    $seedQuestions();
    $secondCount = $movie->questions()->count();

    expect($firstCount)->toBe($secondCount);
});
