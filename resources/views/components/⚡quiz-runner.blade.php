<?php

use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Services\LeaderboardService;
use App\Services\NativeFeedback;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quiz — Quiz App')] class extends Component
{
    public QuizSession $session;

    public int $currentIndex = 0;

    public ?int $selectedChoiceId = null;

    public ?bool $wasCorrect = null;

    public bool $answered = false;

    public string $questionStartedAt = '';

    public function mount(QuizSession $session): void
    {
        if ($session->completed_at) {
            $this->redirect(route('quiz.summary', $session), navigate: true);

            return;
        }

        $this->session = $session;
        $this->currentIndex = $session->answers()->count();
        $this->questionStartedAt = now()->toISOString();
    }

    /** @return Question */
    #[Computed]
    public function question(): Question
    {
        $questionId = $this->session->question_ids[$this->currentIndex];

        return Question::with(['choices' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($questionId);
    }

    public function selectAnswer(int $choiceId): void
    {
        if ($this->answered) {
            return;
        }

        $question = $this->question;
        $correctChoice = $question->choices()->correctAnswer()->first();
        $isCorrect = $correctChoice && $correctChoice->id === $choiceId;

        $timeSpent = (int) now()->diffInSeconds($this->questionStartedAt);

        QuizAnswer::create([
            'quiz_session_id' => $this->session->id,
            'question_id' => $question->id,
            'selected_choice_id' => $choiceId,
            'is_correct' => $isCorrect,
            'answered_at' => now(),
            'time_spent_seconds' => max(1, $timeSpent),
        ]);

        $this->selectedChoiceId = $choiceId;
        $this->wasCorrect = $isCorrect;
        $this->answered = true;

        $feedback = app(NativeFeedback::class);

        if ($isCorrect) {
            $feedback->success('Correct!');
        } else {
            $feedback->error('Not quite!');
        }
    }

    public function nextQuestion(): void
    {
        if (! $this->answered) {
            return;
        }

        $this->currentIndex++;

        if ($this->currentIndex >= $this->session->question_count) {
            $this->completeQuiz();

            return;
        }

        $this->selectedChoiceId = null;
        $this->wasCorrect = null;
        $this->answered = false;
        $this->questionStartedAt = now()->toISOString();
        unset($this->question);
    }

    protected function completeQuiz(): void
    {
        $correctCount = $this->session->answers()->where('is_correct', true)->count();

        $this->session->update([
            'completed_at' => now(),
            'correct_count' => $correctCount,
            'duration_seconds' => (int) $this->session->started_at->diffInSeconds(now()),
        ]);

        $this->session->refresh();
        app(LeaderboardService::class)->submitScore($this->session);

        $this->redirect(route('quiz.summary', $this->session), navigate: true);
    }

    public function title(): string
    {
        return 'Quiz — ' . $this->session->movie->title;
    }
};
?>

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <span class="rounded-full bg-white/80 backdrop-blur-sm px-3 py-1.5 text-sm font-semibold text-gray-600">
                {{ $currentIndex + 1 }} / {{ $session->question_count }}
            </span>
            <span class="text-sm font-semibold text-ocean-500">{{ $session->movie->title }}</span>
        </div>

        {{-- Progress bar --}}
        <div class="mb-6 h-3 rounded-full bg-white/60 backdrop-blur-sm overflow-hidden">
            <div
                class="h-3 rounded-full bg-gradient-to-r from-ocean-400 to-candy-400 transition-all duration-500 ease-out"
                style="width: {{ (($currentIndex + ($answered ? 1 : 0)) / $session->question_count) * 100 }}%"
            ></div>
        </div>

        {{-- Question card --}}
        <div
            class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6"
            x-data="{ showQuestion: true }"
            x-init="showQuestion = true"
            wire:key="question-card-{{ $currentIndex }}"
        >
            <h2
                class="text-lg font-bold text-gray-800 leading-snug animate-fade-in"
                wire:key="question-text-{{ $currentIndex }}"
            >
                {{ $this->question->prompt }}
            </h2>

            <div class="mt-6 grid gap-3">
                @foreach ($this->question->choices as $choiceIndex => $choice)
                    <button
                        wire:key="choice-{{ $choice->id }}"
                        wire:click="selectAnswer({{ $choice->id }})"
                        @disabled($answered)
                        x-data="{ pressed: false }"
                        x-on:click="if (!{{ $answered ? 'true' : 'false' }}) { pressed = true; setTimeout(() => pressed = false, 200) }"
                        :class="pressed ? 'scale-[0.97]' : 'scale-100'"
                        @class([
                            'w-full rounded-2xl border-2 px-5 py-4 text-left font-semibold transition-all duration-200 min-h-[56px]',
                            'border-white bg-white hover:border-ocean-300 hover:shadow-md active:scale-[0.97]' => !$answered,
                            'border-mint-400 bg-mint-50 text-mint-600 animate-pop' => $answered && $choice->is_correct,
                            'border-candy-400 bg-candy-50 text-candy-600 animate-wiggle' => $answered && $selectedChoiceId === $choice->id && !$choice->is_correct,
                            'border-gray-100 bg-gray-50 opacity-40' => $answered && $selectedChoiceId !== $choice->id && !$choice->is_correct,
                        ])
                        style="animation-delay: {{ $choiceIndex * 0.05 }}s"
                    >
                        <span class="mr-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-400">{{ $choice->label }}</span>
                        {{ $choice->text }}
                    </button>
                @endforeach
            </div>

            @if ($answered)
                <div
                    @class([
                        'mt-6 rounded-xl p-4 animate-fade-in-up',
                        'bg-mint-50 text-mint-600 border border-mint-200' => $wasCorrect,
                        'bg-candy-50 text-candy-600 border border-candy-200' => !$wasCorrect,
                    ])
                >
                    <p class="font-bold text-base">{{ $wasCorrect ? 'Correct!' : 'Not quite!' }}</p>
                    @if ($this->question->explanation)
                        <p class="mt-1 text-sm opacity-80">{{ $this->question->explanation }}</p>
                    @endif
                </div>

                <button
                    wire:click="nextQuestion"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="mt-4 w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px] animate-fade-in-up"
                >
                    {{ $currentIndex + 1 < $session->question_count ? 'Next Question' : 'See Results' }}
                </button>
            @endif
        </div>
    </div>
</div>
