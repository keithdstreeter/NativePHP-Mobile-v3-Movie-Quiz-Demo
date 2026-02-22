<?php

use App\Models\Question;
use App\Models\QuestionChoice;
use Illuminate\Database\QueryException;

it('can be created with factory', function () {
    $choice = QuestionChoice::factory()->create();

    expect($choice)->toBeInstanceOf(QuestionChoice::class)
        ->and($choice->exists)->toBeTrue();
});

it('belongs to question', function () {
    $question = Question::factory()->create();
    $choice = QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'A',
    ]);

    expect($choice->question)->toBeInstanceOf(Question::class)
        ->and($choice->question->id)->toBe($question->id);
});

it('enforces unique constraint on question_id and label', function () {
    $question = Question::factory()->create();

    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'A',
    ]);

    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'A',
    ]);
})->throws(QueryException::class);

it('correctAnswer scope returns the correct choice for a question', function () {
    $question = Question::factory()->create();

    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'A',
        'is_correct' => false,
    ]);
    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'B',
        'is_correct' => true,
    ]);
    QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'C',
        'is_correct' => false,
    ]);

    $correct = $question->choices()->correctAnswer()->get();

    expect($correct)->toHaveCount(1)
        ->and($correct->first()->label)->toBe('B')
        ->and($correct->first()->is_correct)->toBeTrue();
});
