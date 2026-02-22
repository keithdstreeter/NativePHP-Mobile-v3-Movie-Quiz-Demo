<?php

use App\Models\Movie;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\NativeFeedback;
use Livewire\Livewire;

beforeEach(function () {
    $this->movie = Movie::factory()->create(['slug' => 'feedback-test']);

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

    UserSetting::set('age_group_id', (string) $this->movie->age_group_id);
});

it('calls NativeFeedback success on correct answer', function () {
    $mock = Mockery::mock(NativeFeedback::class);
    $mock->shouldReceive('success')->once()->with('Correct!');
    $mock->shouldNotReceive('error');
    app()->instance(NativeFeedback::class, $mock);

    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $correctChoice = $firstQuestion->choices()->correctAnswer()->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $correctChoice->id);
});

it('calls NativeFeedback error on wrong answer', function () {
    $mock = Mockery::mock(NativeFeedback::class);
    $mock->shouldReceive('error')->once()->with('Not quite!');
    $mock->shouldNotReceive('success');
    app()->instance(NativeFeedback::class, $mock);

    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $wrongChoice = $firstQuestion->choices()->where('is_correct', false)->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $wrongChoice->id);
});

it('does not break the app when NativePHP is unavailable', function () {
    $session = QuizSession::factory()->create([
        'movie_id' => $this->movie->id,
        'question_count' => 3,
        'question_ids' => $this->questions->pluck('id')->toArray(),
    ]);

    $firstQuestion = Question::find($session->question_ids[0]);
    $choice = $firstQuestion->choices->first();

    Livewire::test('quiz-runner', ['session' => $session])
        ->call('selectAnswer', $choice->id)
        ->assertSet('answered', true)
        ->assertSuccessful();
});
