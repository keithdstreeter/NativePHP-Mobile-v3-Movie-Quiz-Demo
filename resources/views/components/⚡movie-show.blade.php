<?php

use App\Models\Movie;
use App\Models\QuizSession;
use App\Models\UserSetting;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Movie — Quiz App')] class extends Component
{
    public Movie $movie;

    public int $questionCount = 10;

    public int $availableQuestions = 0;

    public function mount(string $slug): void
    {
        $this->movie = Movie::where('slug', $slug)->firstOrFail();
        $this->availableQuestions = $this->movie->questions()->active()->count();
        $this->questionCount = min(10, $this->availableQuestions);
    }

    public function startQuiz(): void
    {
        $questionIds = $this->movie->questions()
            ->active()
            ->inRandomOrder()
            ->limit($this->questionCount)
            ->pluck('id')
            ->toArray();

        $session = QuizSession::create([
            'movie_id' => $this->movie->id,
            'age_group_id' => (int) UserSetting::get('age_group_id') ?: $this->movie->age_group_id,
            'question_count' => count($questionIds),
            'correct_count' => 0,
            'started_at' => now(),
            'question_ids' => $questionIds,
        ]);

        $this->redirect(route('quiz.play', $session), navigate: true);
    }

    public function title(): string
    {
        return $this->movie->title . ' — Quiz App';
    }
};
?>

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6">
            <a href="{{ route('movies') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Back to Movies</a>
        </div>

        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up">
            <h1 class="text-2xl font-bold text-gray-800">{{ $movie->title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $movie->release_year }}</p>

            @if ($movie->description)
                <p class="mt-4 text-gray-600 leading-relaxed">{{ $movie->description }}</p>
            @endif

            @if ($availableQuestions > 0)
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <label for="question-count" class="block text-sm font-semibold text-gray-700 mb-2">
                        Number of questions
                    </label>
                    <select
                        id="question-count"
                        wire:model="questionCount"
                        class="w-full rounded-xl border-2 border-gray-100 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-ocean-400 focus:ring-ocean-400 min-h-[48px]"
                    >
                        @for ($i = 1; $i <= $availableQuestions; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>

                    <button
                        wire:click="startQuiz"
                        x-data="{ pressed: false }"
                        x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                        :class="pressed ? 'scale-95' : 'scale-100'"
                        class="mt-4 w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                    >
                        Start Quiz
                    </button>
                </div>
            @else
                <p class="mt-6 text-center text-gray-500">No questions available for this movie yet.</p>
            @endif
        </div>
    </div>
</div>
