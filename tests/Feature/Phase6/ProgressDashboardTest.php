<?php

use App\Models\Movie;
use App\Models\QuizSession;
use Livewire\Livewire;

use function Pest\Laravel\get;

it('renders the ProgressDashboard page', function () {
    get('/progress')->assertSuccessful();
});

it('shows total quizzes count accurately', function () {
    $movie = Movie::factory()->create();

    QuizSession::factory()->completed()->count(3)->create(['movie_id' => $movie->id]);
    QuizSession::factory()->create(['movie_id' => $movie->id, 'completed_at' => null]);

    Livewire::test('progress-dashboard')
        ->assertSee('3')
        ->assertSeeInOrder(['3', 'Quizzes Played']);
});

it('calculates accuracy percentage correctly', function () {
    $movie = Movie::factory()->create();

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 7,
    ]);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 3,
    ]);

    // 10 correct out of 20 total = 50%
    Livewire::test('progress-dashboard')
        ->assertSee('50%')
        ->assertSeeInOrder(['50%', 'Accuracy']);
});

it('displays best score per movie correctly', function () {
    $movie = Movie::factory()->create(['title' => 'Test Movie']);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 5,
    ]);

    QuizSession::factory()->completed()->create([
        'movie_id' => $movie->id,
        'question_count' => 10,
        'correct_count' => 9,
    ]);

    Livewire::test('progress-dashboard')
        ->assertSee('Test Movie')
        ->assertSee('90%')
        ->assertSee('2 attempts');
});

it('shows no data state when no quizzes played', function () {
    Livewire::test('progress-dashboard')
        ->assertSee('No quizzes played yet.')
        ->assertSee('Start a Quiz');
});
