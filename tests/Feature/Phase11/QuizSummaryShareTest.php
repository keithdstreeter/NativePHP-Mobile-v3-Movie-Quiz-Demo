<?php

use App\Models\Movie;
use App\Models\QuizSession;
use App\Services\NativeFeedback;
use Livewire\Livewire;

it('displays Share Results button on quiz summary', function () {
    $movie = Movie::factory()->create();
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 8,
    ]);

    Livewire::test('quiz-summary', ['session' => $session])
        ->assertSee('Share Results');
});

it('triggers share with correct score text', function () {
    $movie = Movie::factory()->create(['title' => 'Frozen']);
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 8,
    ]);

    $mock = Mockery::mock(NativeFeedback::class);
    $mock->shouldReceive('share')
        ->once()
        ->with('Quiz Results', 'I scored 8/10 (80%) on Frozen!');
    app()->instance(NativeFeedback::class, $mock);

    Livewire::test('quiz-summary', ['session' => $session])
        ->call('shareResults');
});

it('share fallback works when NativePHP is unavailable', function () {
    $movie = Movie::factory()->create();
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 8,
    ]);

    Livewire::test('quiz-summary', ['session' => $session])
        ->call('shareResults')
        ->assertSuccessful();
});
