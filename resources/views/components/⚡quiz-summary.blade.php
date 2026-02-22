<?php

use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\NativeFeedback;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quiz Results — Quiz App')] class extends Component
{
    public QuizSession $session;

    public function mount(QuizSession $session): void
    {
        if (! $session->completed_at) {
            $this->redirect(route('quiz.play', $session), navigate: true);

            return;
        }

        $this->session = $session;
    }

    public function playAgain(): void
    {
        $movie = $this->session->movie;
        $questionIds = $movie->questions()
            ->active()
            ->inRandomOrder()
            ->limit($this->session->question_count)
            ->pluck('id')
            ->toArray();

        $newSession = QuizSession::create([
            'movie_id' => $movie->id,
            'age_group_id' => (int) UserSetting::get('age_group_id') ?: $movie->age_group_id,
            'question_count' => count($questionIds),
            'correct_count' => 0,
            'started_at' => now(),
            'question_ids' => $questionIds,
        ]);

        $this->redirect(route('quiz.play', $newSession), navigate: true);
    }

    public function shareResults(): void
    {
        $percentage = $this->session->question_count > 0
            ? round(($this->session->correct_count / $this->session->question_count) * 100)
            : 0;

        $text = "I scored {$this->session->correct_count}/{$this->session->question_count} ({$percentage}%) on {$this->session->movie->title}!";

        app(NativeFeedback::class)->share('Quiz Results', $text);
    }

    public function title(): string
    {
        return 'Results — ' . $this->session->movie->title;
    }
};
?>

@php
    $percentage = $session->question_count > 0 ? round(($session->correct_count / $session->question_count) * 100) : 0;
@endphp

<div class="min-h-screen px-4 py-8 flex items-center justify-center">
    <div class="mx-auto w-full max-w-lg">
        <div
            class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-8 text-center"
            x-data="{
                shown: false,
                count: 0,
                target: {{ $percentage }},
                animateCount() {
                    if (this.count < this.target) {
                        this.count += Math.ceil(this.target / 30);
                        if (this.count > this.target) this.count = this.target;
                        setTimeout(() => this.animateCount(), 30);
                    }
                }
            }"
            x-init="setTimeout(() => { shown = true; animateCount(); }, 200)"
        >
            <div
                x-show="shown"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 scale-75"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h1 class="text-2xl font-bold text-gray-800">Quiz Complete!</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $session->movie->title }}</p>

                <div class="mt-8">
                    <div
                        class="text-6xl font-bold bg-gradient-to-r from-ocean-500 to-candy-500 bg-clip-text text-transparent"
                        x-text="count + '%'"
                    >
                        0%
                    </div>
                    <p class="mt-2 text-gray-500">
                        {{ $session->correct_count }} out of {{ $session->question_count }} correct
                    </p>
                </div>

                @if ($percentage >= 80)
                    <p class="mt-4 text-lg font-bold text-mint-500 animate-bounce-in" style="animation-delay: 0.8s; animation-fill-mode: both">
                        Amazing job!
                    </p>
                @elseif ($percentage >= 50)
                    <p class="mt-4 text-lg font-bold text-ocean-500 animate-bounce-in" style="animation-delay: 0.8s; animation-fill-mode: both">
                        Good effort!
                    </p>
                @else
                    <p class="mt-4 text-lg font-bold text-candy-500 animate-bounce-in" style="animation-delay: 0.8s; animation-fill-mode: both">
                        Keep practicing!
                    </p>
                @endif

                @if ($session->duration_seconds)
                    <p class="mt-3 text-sm text-gray-400">
                        Time: {{ gmdate('i:s', $session->duration_seconds) }}
                    </p>
                @endif
            </div>

            <div class="mt-8 grid gap-3" x-show="shown" x-transition:enter="transition ease-out duration-300 delay-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <button
                    wire:click="playAgain"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                >
                    Play Again
                </button>

                <button
                    wire:click="shareResults"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full rounded-2xl border-2 border-ocean-300 bg-white/80 px-6 py-5 text-lg font-bold text-ocean-500 hover:border-ocean-400 hover:shadow-md transition-all duration-200 min-h-[56px]"
                >
                    Share Results
                </button>

                <a
                    href="{{ route('leaderboard') }}"
                    wire:navigate
                    class="block w-full rounded-2xl border-2 border-ocean-300 bg-white/80 px-6 py-5 text-center text-lg font-bold text-ocean-500 hover:border-ocean-400 hover:shadow-md transition-all duration-200 min-h-[56px]"
                >
                    View Leaderboard
                </a>

                <a
                    href="{{ route('movies') }}"
                    wire:navigate
                    class="block w-full rounded-2xl border-2 border-white bg-white/60 px-6 py-5 text-center text-lg font-bold text-gray-600 hover:border-ocean-300 hover:shadow-md transition-all duration-200 min-h-[56px]"
                >
                    Back to Movies
                </a>
            </div>
        </div>
    </div>
</div>
