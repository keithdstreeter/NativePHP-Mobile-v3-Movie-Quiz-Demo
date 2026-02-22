<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\QuizAnswer;
use App\Models\QuizSession;

it('can be created with factory', function () {
    $session = QuizSession::factory()->create();

    expect($session)->toBeInstanceOf(QuizSession::class)
        ->and($session->exists)->toBeTrue();
});

it('belongs to movie and age group', function () {
    $movie = Movie::factory()->create();
    $ageGroup = AgeGroup::factory()->create();
    $session = QuizSession::factory()->create([
        'movie_id' => $movie->id,
        'age_group_id' => $ageGroup->id,
    ]);

    expect($session->movie)->toBeInstanceOf(Movie::class)
        ->and($session->movie->id)->toBe($movie->id)
        ->and($session->ageGroup)->toBeInstanceOf(AgeGroup::class)
        ->and($session->ageGroup->id)->toBe($ageGroup->id);
});

it('has many quiz answers', function () {
    $session = QuizSession::factory()->create();
    QuizAnswer::factory()->count(3)->create(['quiz_session_id' => $session->id]);

    expect($session->answers)->toHaveCount(3)
        ->each->toBeInstanceOf(QuizAnswer::class);
});

it('casts question_ids to array', function () {
    $session = QuizSession::factory()->create([
        'question_ids' => [1, 2, 3],
    ]);

    $session->refresh();

    expect($session->question_ids)->toBeArray()
        ->and($session->question_ids)->toBe([1, 2, 3]);
});

it('allows completed_at and duration_seconds to be nullable on creation', function () {
    $session = QuizSession::factory()->create([
        'completed_at' => null,
        'duration_seconds' => null,
    ]);

    expect($session->completed_at)->toBeNull()
        ->and($session->duration_seconds)->toBeNull();
});
