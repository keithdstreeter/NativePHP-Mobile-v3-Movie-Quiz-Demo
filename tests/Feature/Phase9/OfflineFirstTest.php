<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\NativeFeedback;
use Livewire\Livewire;

use function Pest\Laravel\get;

beforeEach(function () {
    $this->ageGroup = AgeGroup::factory()->create();
    $this->movie = Movie::factory()->create([
        'slug' => 'test-movie',
        'age_group_id' => $this->ageGroup->id,
    ]);

    $this->questions = Question::factory()
        ->count(3)
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

    UserSetting::set('age_group_id', (string) $this->ageGroup->id);
});

it('completes a full quiz flow with seeded SQLite data', function () {
    // Start quiz from movie page
    $component = Livewire::test('movie-show', ['slug' => 'test-movie'])
        ->set('questionCount', 2)
        ->call('startQuiz');

    $session = QuizSession::first();
    expect($session)->not->toBeNull();

    // Answer both questions
    $q1 = Question::find($session->question_ids[0]);
    $q2 = Question::find($session->question_ids[1]);

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $q1->choices()->correctAnswer()->first()->id)
        ->call('nextQuestion')
        ->call('selectAnswer', $q2->choices()->correctAnswer()->first()->id)
        ->call('nextQuestion')
        ->assertRedirect(route('quiz.summary', $session));

    $session->refresh();
    expect($session->completed_at)->not->toBeNull()
        ->and($session->correct_count)->toBe(2);

    // View summary
    Livewire::test('quiz-summary', ['session' => $session])
        ->assertSee('2 out of 2 correct');
});

it('renders all pages without external resource dependencies', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'age_group_id' => $this->ageGroup->id,
        'question_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $completedSession = QuizSession::factory()->completed()->create([
        'movie_id' => $this->movie->id,
        'age_group_id' => $this->ageGroup->id,
        'question_count' => 3,
        'correct_count' => 2,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    get('/')->assertSuccessful();
    get('/movies')->assertSuccessful();
    get('/movies/test-movie')->assertSuccessful();
    get("/quiz/{$session->id}")->assertSuccessful();
    get("/quiz/{$completedSession->id}/summary")->assertSuccessful();
    get('/progress')->assertSuccessful();
    get('/settings')->assertSuccessful();
});

it('resolves NativeFeedback as a singleton from the container', function () {
    $instance1 = app(NativeFeedback::class);
    $instance2 = app(NativeFeedback::class);

    expect($instance1)->toBeInstanceOf(NativeFeedback::class)
        ->and($instance1)->toBe($instance2);
});
