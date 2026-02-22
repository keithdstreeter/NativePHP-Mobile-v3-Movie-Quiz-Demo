<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\QuizSession;
use App\Models\UserSetting;

use function Pest\Laravel\get;

it('shows number of quiz attempts on movie card', function () {
    $ageGroup = AgeGroup::factory()->create();
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    QuizSession::factory()->completed()->count(3)->create([
        'movie_id' => $movie->id,
        'age_group_id' => $ageGroup->id,
        'question_count' => 10,
        'correct_count' => 5,
    ]);

    get('/movies')->assertSee('3 attempts');
});

it('shows best score percentage on movie card', function () {
    $ageGroup = AgeGroup::factory()->create();
    $movie = Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'age_group_id' => $ageGroup->id,
        'question_count' => 10,
        'correct_count' => 4,
    ]);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'age_group_id' => $ageGroup->id,
        'question_count' => 10,
        'correct_count' => 7,
    ]);

    get('/movies')->assertSee('70%');
});

it('shows Not played for movies with no attempts', function () {
    $ageGroup = AgeGroup::factory()->create();
    Movie::factory()->create(['age_group_id' => $ageGroup->id]);
    UserSetting::set('age_group_id', (string) $ageGroup->id);

    get('/movies')->assertSee('New');
});
