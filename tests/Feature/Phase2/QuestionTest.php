<?php

use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;

it('can be created with factory', function () {
    $question = Question::factory()->create();

    expect($question)->toBeInstanceOf(Question::class)
        ->and($question->exists)->toBeTrue();
});

it('belongs to movie', function () {
    $movie = Movie::factory()->create();
    $question = Question::factory()->create(['movie_id' => $movie->id]);

    expect($question->movie)->toBeInstanceOf(Movie::class)
        ->and($question->movie->id)->toBe($movie->id);
});

it('has many question choices', function () {
    $question = Question::factory()->create();
    QuestionChoice::factory()->count(4)->sequence(
        ['label' => 'A'],
        ['label' => 'B'],
        ['label' => 'C'],
        ['label' => 'D'],
    )->create(['question_id' => $question->id]);

    expect($question->choices)->toHaveCount(4)
        ->each->toBeInstanceOf(QuestionChoice::class);
});

it('scope active returns only is_active true records', function () {
    Question::factory()->count(2)->create(['is_active' => true]);
    Question::factory()->count(3)->create(['is_active' => false]);

    expect(Question::active()->count())->toBe(2);
});
