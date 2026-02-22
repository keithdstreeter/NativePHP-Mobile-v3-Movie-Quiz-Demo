<?php

use App\Models\Movie;
use App\Models\QuizSession;
use App\Services\NativeFeedback;
use Livewire\Livewire;

it('displays share button on progress dashboard when quizzes exist', function () {
    $movie = Movie::factory()->create();
    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 7,
    ]);

    Livewire::test('progress-dashboard')
        ->assertSee('Share Stats');
});

it('does not display share button when no quizzes played', function () {
    Livewire::test('progress-dashboard')
        ->assertDontSee('Share Stats');
});

it('share action includes overall accuracy and total quizzes played', function () {
    $movie = Movie::factory()->create();
    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 8,
    ]);

    $mock = Mockery::mock(NativeFeedback::class);
    $mock->shouldReceive('share')
        ->once()
        ->withArgs(function (string $title, string $text) {
            return $title === 'My Quiz Stats'
                && str_contains($text, '1 quizzes')
                && str_contains($text, '80%');
        });
    app()->instance(NativeFeedback::class, $mock);

    Livewire::test('progress-dashboard')
        ->call('shareStats');
});
