<?php

use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\UserSetting;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->movie = Movie::factory()->create(['slug' => 'test-movie']);

    $this->questions = Question::factory()
        ->count(5)
        ->create(['movie_id' => $this->movie->id])
        ->each(function (Question $question, int $index) {
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

// Phase 5.1: Quiz Session Creation

it('creates a QuizSession when starting a quiz from MovieShow', function () {
    Livewire::test('movie-show', ['slug' => 'test-movie'])
        ->set('questionCount', 3)
        ->call('startQuiz');

    expect(QuizSession::count())->toBe(1);

    $session = QuizSession::first();
    expect($session->movie_id)->toBe($this->movie->id)
        ->and($session->question_count)->toBe(3)
        ->and($session->started_at)->not->toBeNull()
        ->and($session->completed_at)->toBeNull();
});

it('stores randomized question_ids as JSON', function () {
    Livewire::test('movie-show', ['slug' => 'test-movie'])
        ->set('questionCount', 5)
        ->call('startQuiz');

    $session = QuizSession::first();
    expect($session->question_ids)->toBeArray()
        ->and($session->question_ids)->toHaveCount(5);
});

it('creates a session with the requested question count', function () {
    Livewire::test('movie-show', ['slug' => 'test-movie'])
        ->set('questionCount', 3)
        ->call('startQuiz');

    $session = QuizSession::first();
    expect($session->question_count)->toBe(3)
        ->and($session->question_ids)->toHaveCount(3);
});

it('renders QuizRunner with the first question', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    get("/quiz/{$session->id}")
        ->assertSuccessful();

    $firstQuestion = Question::find($session->question_ids[0]);

    Livewire::test('quiz-runner', ['session' => $session])
        ->assertSee($firstQuestion->prompt);
});

// Phase 5.2: Answering Questions

it('creates a QuizAnswer record when selecting a choice', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $choice = $firstQuestion->choices->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $choice->id);

    expect(QuizAnswer::count())->toBe(1);

    $answer = QuizAnswer::first();
    expect($answer->quiz_session_id)->toBe($session->id)
        ->and($answer->question_id)->toBe($firstQuestion->id)
        ->and($answer->selected_choice_id)->toBe($choice->id);
});

it('sets is_correct properly based on the choice', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $correctChoice = $firstQuestion->choices()->correctAnswer()->first();
    $wrongChoice = $firstQuestion->choices()->where('is_correct', false)->first();

    // Correct answer
    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $correctChoice->id)
        ->assertSet('wasCorrect', true);

    expect(QuizAnswer::first()->is_correct)->toBeTrue();

    // Wrong answer (new session)
    QuizAnswer::query()->delete();
    $session2 = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    Livewire::test('quiz-runner', ['session' => $session2])
        ->call('selectAnswer', $wrongChoice->id)
        ->assertSet('wasCorrect', false);

    expect(QuizAnswer::first()->is_correct)->toBeFalse();
});

it('shows feedback after answering', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $correctChoice = $firstQuestion->choices()->correctAnswer()->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $correctChoice->id)
        ->assertSee('Correct!');
});

it('displays explanation after answering', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $choice = $firstQuestion->choices->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $choice->id)
        ->assertSee($firstQuestion->explanation);
});

it('cannot answer the same question twice in a session', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 5,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $choice = $firstQuestion->choices->first();
    $otherChoice = $firstQuestion->choices->last();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $choice->id)
        ->call('selectAnswer', $otherChoice->id);

    expect(QuizAnswer::count())->toBe(1);
});

// Phase 5.3: Quiz Completion

it('sets completed_at on QuizSession after all questions answered', function () {
    $questions = $this->questions->take(2);
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 2,
        'question_ids' => $questions->pluck('id')->toArray(),
    ]);

    $q1 = Question::find($session->question_ids[0]);
    $q2 = Question::find($session->question_ids[1]);

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $q1->choices->first()->id)
        ->call('nextQuestion')
        ->call('selectAnswer', $q2->choices->first()->id)
        ->call('nextQuestion');

    $session->refresh();
    expect($session->completed_at)->not->toBeNull();
});

it('calculates correct_count matching actual correct answers', function () {
    $questions = $this->questions->take(2);
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 2,
        'question_ids' => $questions->pluck('id')->toArray(),
    ]);

    $q1 = Question::find($session->question_ids[0]);
    $q2 = Question::find($session->question_ids[1]);

    $correctChoice1 = $q1->choices()->correctAnswer()->first();
    $wrongChoice2 = $q2->choices()->where('is_correct', false)->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $correctChoice1->id)
        ->call('nextQuestion')
        ->call('selectAnswer', $wrongChoice2->id)
        ->call('nextQuestion');

    $session->refresh();
    expect($session->correct_count)->toBe(1);
});

it('calculates duration_seconds from started_at to completed_at', function () {
    $questions = $this->questions->take(1);
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 1,
        'question_ids' => $questions->pluck('id')->toArray(),
        'started_at' => now()->subSeconds(30),
    ]);

    $q1 = Question::find($session->question_ids[0]);

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $q1->choices->first()->id)
        ->call('nextQuestion');

    $session->refresh();
    expect($session->duration_seconds)->toBeGreaterThanOrEqual(30);
});

it('redirects to summary after last question', function () {
    $questions = $this->questions->take(1);
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 1,
        'question_ids' => $questions->pluck('id')->toArray(),
    ]);

    $q1 = Question::find($session->question_ids[0]);

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $q1->choices->first()->id)
        ->call('nextQuestion')
        ->assertRedirect(route('quiz.summary', $session));
});
