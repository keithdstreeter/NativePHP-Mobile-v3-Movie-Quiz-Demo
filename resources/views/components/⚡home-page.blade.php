<?php

use App\Models\AgeGroup;
use App\Models\Movie;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quiz App')] class extends Component
{
    public ?int $selectedAgeGroupId = null;

    public function mount(): void
    {
        $savedId = UserSetting::get('age_group_id');

        if ($savedId) {
            $this->selectedAgeGroupId = (int) $savedId;
        }
    }

    public function selectAgeGroup(int $ageGroupId): void
    {
        $this->selectedAgeGroupId = $ageGroupId;
        UserSetting::set('age_group_id', (string) $ageGroupId);
        unset($this->hasMovies);
    }

    public function quickStart(): void
    {
        if (! $this->selectedAgeGroupId) {
            return;
        }

        $this->redirect(route('movies'));
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AgeGroup> */
    #[Computed]
    public function ageGroups(): \Illuminate\Database\Eloquent\Collection
    {
        return AgeGroup::active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function hasMovies(): bool
    {
        if (! $this->selectedAgeGroupId) {
            return false;
        }

        return Movie::active()->where('age_group_id', $this->selectedAgeGroupId)->exists();
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg text-center" x-data="{ shown: false }" x-init="$nextTick(() => shown = true)">
        <div x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <h1 class="text-5xl font-bold bg-gradient-to-r from-candy-500 via-ocean-500 to-mint-500 bg-clip-text text-transparent mb-2">
                Quiz App
            </h1>
            <p class="text-lg text-gray-500 mb-10">Test your movie knowledge!</p>
        </div>

        <h2 class="text-xl font-semibold text-gray-700 mb-4">Choose Your Age Group</h2>

        <div class="grid gap-3">
            @foreach ($this->ageGroups as $index => $ageGroup)
                <button
                    wire:key="age-group-{{ $ageGroup->id }}"
                    wire:click="selectAgeGroup({{ $ageGroup->id }})"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    @class([
                        'w-full rounded-2xl px-6 py-5 text-lg font-bold transition-all duration-200 min-h-[56px]',
                        'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-lg shadow-ocean-200 ring-2 ring-ocean-300' => $selectedAgeGroupId === $ageGroup->id,
                        'bg-white/80 text-gray-700 border-2 border-white hover:border-ocean-300 hover:shadow-md backdrop-blur-sm' => $selectedAgeGroupId !== $ageGroup->id,
                    ])
                    style="animation: fade-in-up 0.4s ease-out {{ $index * 0.08 }}s both"
                >
                    {{ $ageGroup->label }}
                </button>
            @endforeach
        </div>

        @if ($selectedAgeGroupId && $this->hasMovies)
            <div class="mt-8 animate-fade-in-up">
                <button
                    wire:click="quickStart"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full rounded-2xl bg-gradient-to-r from-mint-500 to-mint-400 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-mint-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                >
                    Browse Movies
                </button>
            </div>
        @endif

        <div class="mt-8 flex items-center justify-center gap-6">
            <a href="{{ route('progress') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">
                Progress
            </a>
            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
            <a href="{{ route('leaderboard') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">
                Leaderboard
            </a>
            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
            <a href="{{ route('settings') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">
                Settings
            </a>
        </div>
    </div>
</div>
