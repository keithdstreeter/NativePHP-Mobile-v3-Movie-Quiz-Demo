<?php

use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizSession;
use App\Models\UserSetting;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->movie = Movie::factory()->create(['slug' => 'test-movie']);

    $this->questions = Question::factory()
        ->count(5)
        ->create(['movie_id' => $this->movie->id])
        ->each(function (Question $question) {
            QuestionChoice::factory()->create([
                'question_id' => $question->id,
                'label' => 'A',
                'is_correct' => true,
                'sort_order' => 0,
            ]);

            foreach (['B', 'C', 'D'] as $i => $label) {
                QuestionChoice::factory()->create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'is_correct' => false,
                    'sort_order' => $i + 1,
                ]);
            }
        });

    UserSetting::set('age_group_id', (string) $this->movie->age_group_id);
});

it('renders QuizSummary with correct score', function () {
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'correct_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    get("/quiz/{$session->id}/summary")
        ->assertSuccessful()
        ->assertSee('Quiz Complete!');
});

it('shows correct count, total count, and percentage', function () {
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'correct_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    Livewire::test('quiz-summary', ['session' => $session])
        ->assertSee('3 out of 5 correct')
        ->assertSeeHtml('target: 60');
});

it('starts a new session for the same movie via Play Again', function () {
    $session = QuizSession::factory()->completed()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'correct_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    Livewire::test('quiz-summary', ['session' => $session])
        ->call('playAgain');

    expect(QuizSession::count())->toBe(2);

    $newSession = QuizSession::latest('id')->first();
    expect($newSession->movie_id)->toBe($this->movie->id)
        ->and($newSession->completed_at)->toBeNull();
});

it('redirects incomplete session back to QuizRunner', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
        'completed_at' => null,
    ]);

    Livewire::test('quiz-summary', ['session' => $session])
        ->assertRedirect(route('quiz.play', $session));
});
