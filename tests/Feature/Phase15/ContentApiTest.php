<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;

it('returns all questions when no since param', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '4-6']);
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    $question = Question::factory()->create(['movie_id' => $movie->id]);
    QuestionChoice::factory()->correct()->create(['question_id' => $question->id, 'label' => 'A']);
    QuestionChoice::factory()->create(['question_id' => $question->id, 'label' => 'B']);

    $response = $this->getJson('/api/questions')->assertSuccessful();

    expect($response->json('movies'))->toHaveCount(1)
        ->and($response->json('movies.0.slug'))->toBe($movie->slug)
        ->and($response->json('movies.0.age_group_code'))->toBe('4-6')
        ->and($response->json('movies.0.questions'))->toHaveCount(1)
        ->and($response->json('movies.0.questions.0.choices'))->toHaveCount(2);
});

it('returns only questions created after since timestamp', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '7-9']);
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);

    $oldQuestion = Question::factory()->create([
        'movie_id' => $movie->id,
        'created_at' => now()->subDays(2),
    ]);

    $newQuestion = Question::factory()->create([
        'movie_id' => $movie->id,
        'created_at' => now(),
    ]);
    QuestionChoice::factory()->correct()->create(['question_id' => $newQuestion->id, 'label' => 'A']);

    $since = now()->subDay()->toIso8601String();

    $response = $this->getJson("/api/questions?since={$since}")->assertSuccessful();

    expect($response->json('movies'))->toHaveCount(1)
        ->and($response->json('movies.0.questions'))->toHaveCount(1)
        ->and($response->json('movies.0.questions.0.prompt'))->toBe($newQuestion->prompt);
});

it('excludes movies with no matching questions when since is provided', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '4-6']);

    $movieWithOld = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    Question::factory()->create([
        'movie_id' => $movieWithOld->id,
        'created_at' => now()->subDays(5),
    ]);

    $movieWithNew = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    $newQuestion = Question::factory()->create([
        'movie_id' => $movieWithNew->id,
        'created_at' => now(),
    ]);
    QuestionChoice::factory()->correct()->create(['question_id' => $newQuestion->id, 'label' => 'A']);

    $since = now()->subDay()->toIso8601String();

    $response = $this->getJson("/api/questions?since={$since}")->assertSuccessful();

    expect($response->json('movies'))->toHaveCount(1)
        ->and($response->json('movies.0.slug'))->toBe($movieWithNew->slug);
});

it('includes timestamp in response', function () {
    $response = $this->getJson('/api/questions')->assertSuccessful();

    expect($response->json('timestamp'))->not->toBeNull();
});

it('includes movie data with questions and choices', function () {
    $ageGroup = AgeGroup::factory()->create(['code' => '10-12']);
    $movie = Movie::factory()->create([
        'age_group_id' => $ageGroup->id,
        'title' => 'Test Movie',
        'slug' => 'test-movie',
        'release_year' => 2020,
    ]);
    $question = Question::factory()->create([
        'movie_id' => $movie->id,
        'prompt' => 'What is the answer?',
        'difficulty' => 'easy',
    ]);
    QuestionChoice::factory()->correct()->create([
        'question_id' => $question->id,
        'label' => 'A',
        'text' => 'Correct answer',
    ]);
    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'B',
        'text' => 'Wrong answer',
    ]);

    $response = $this->getJson('/api/questions')->assertSuccessful();

    $movieData = $response->json('movies.0');
    expect($movieData['title'])->toBe('Test Movie')
        ->and($movieData['slug'])->toBe('test-movie')
        ->and($movieData['age_group_code'])->toBe('10-12')
        ->and($movieData['release_year'])->toBe(2020)
        ->and($movieData['questions'][0]['prompt'])->toBe('What is the answer?')
        ->and($movieData['questions'][0]['difficulty'])->toBe('easy')
        ->and($movieData['questions'][0]['choices'])->toHaveCount(2);
});
