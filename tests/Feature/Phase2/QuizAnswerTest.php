<?php

use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizAnswer;
use App\Models\QuizSession;

it('can be created with factory', function () {
    $answer = QuizAnswer::factory()->create();

    expect($answer)->toBeInstanceOf(QuizAnswer::class)
        ->and($answer->exists)->toBeTrue();
});

it('belongs to quiz session, question, and question choice', function () {
    $session = QuizSession::factory()->create();
    $question = Question::factory()->create();
    $choice = QuestionChoice::factory()->create([
        'question_id' => $question->id,
        'label' => 'A',
    ]);

    $answer = QuizAnswer::factory()->create([
        'quiz_session_id' => $session->id,
        'question_id' => $question->id,
        'selected_choice_id' => $choice->id,
    ]);

    expect($answer->quizSession)->toBeInstanceOf(QuizSession::class)
        ->and($answer->quizSession->id)->toBe($session->id)
        ->and($answer->question)->toBeInstanceOf(Question::class)
        ->and($answer->question->id)->toBe($question->id)
        ->and($answer->selectedChoice)->toBeInstanceOf(QuestionChoice::class)
        ->and($answer->selectedChoice->id)->toBe($choice->id);
});
