<?php

use App\Models\Movie;
use App\Models\QuizSession;
use App\Services\NativeFeedback;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Progress — Quiz App')] class extends Component
{
    /** @return int */
    #[Computed]
    public function totalQuizzes(): int
    {
        return QuizSession::whereNotNull('completed_at')->count();
    }

    /** @return float */
    #[Computed]
    public function accuracyPercentage(): float
    {
        $totals = QuizSession::whereNotNull('completed_at')
            ->selectRaw('SUM(correct_count) as total_correct, SUM(question_count) as total_questions')
            ->first();

        if (! $totals->total_questions) {
            return 0;
        }

        return round(($totals->total_correct / $totals->total_questions) * 100, 1);
    }

    public function shareStats(): void
    {
        $text = "I've played {$this->totalQuizzes} quizzes with {$this->accuracyPercentage}% accuracy!";

        app(NativeFeedback::class)->share('My Quiz Stats', $text);
    }

    /** @return \Illuminate\Support\Collection<int, object> */
    #[Computed]
    public function movieStats(): \Illuminate\Support\Collection
    {
        return QuizSession::query()
            ->whereNotNull('completed_at')
            ->join('movies', 'movies.id', '=', 'quiz_sessions.movie_id')
            ->selectRaw('movies.id, movies.title, movies.slug, COUNT(*) as attempts, MAX(quiz_sessions.correct_count * 100 / quiz_sessions.question_count) as best_score, MAX(quiz_sessions.completed_at) as last_played')
            ->groupBy('movies.id', 'movies.title', 'movies.slug')
            ->orderByDesc('last_played')
            ->get();
    }
};
?>

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Progress</h1>
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Home</a>
        </div>

        @if ($this->totalQuizzes > 0)
            <div class="mb-6 flex justify-end">
                <button
                    wire:click="shareStats"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="rounded-2xl border-2 border-ocean-300 bg-white/80 px-5 py-3 text-sm font-bold text-ocean-500 hover:border-ocean-400 hover:shadow-md transition-all duration-200 min-h-[44px]"
                >
                    Share Stats
                </button>
            </div>
        @endif

        @if ($this->totalQuizzes === 0)
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-10 text-center animate-fade-in">
                <p class="text-lg text-gray-500">No quizzes played yet.</p>
                <a
                    href="{{ route('movies') }}"
                    wire:navigate
                    class="mt-4 inline-block rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-4 text-sm font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[48px]"
                >
                    Start a Quiz
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-5 text-center animate-fade-in-up" style="animation-delay: 0s">
                    <div class="text-3xl font-bold text-ocean-500">{{ $this->totalQuizzes }}</div>
                    <p class="mt-1 text-sm text-gray-500">Quizzes Played</p>
                </div>
                <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-5 text-center animate-fade-in-up" style="animation-delay: 0.1s">
                    <div class="text-3xl font-bold text-mint-500">{{ $this->accuracyPercentage }}%</div>
                    <p class="mt-1 text-sm text-gray-500">Accuracy</p>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-gray-700 mb-3">Per Movie</h2>
            <div class="grid gap-4">
                @foreach ($this->movieStats as $index => $stat)
                    <div
                        wire:key="stat-{{ $stat->id }}"
                        class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-5"
                        style="animation: fade-in-up 0.4s ease-out {{ ($index * 0.08) + 0.2 }}s both"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800">{{ $stat->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $stat->attempts }} {{ Str::plural('attempt', $stat->attempts) }}
                                    &middot; Last played {{ \Carbon\Carbon::parse($stat->last_played)->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-bold text-mint-500">{{ round($stat->best_score) }}%</div>
                                <p class="text-xs text-gray-400">Best</p>
                            </div>
                        </div>
                        <div class="mt-3 h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div
                                class="h-2.5 rounded-full bg-gradient-to-r from-mint-400 to-mint-500 transition-all duration-700"
                                style="width: {{ round($stat->best_score) }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
