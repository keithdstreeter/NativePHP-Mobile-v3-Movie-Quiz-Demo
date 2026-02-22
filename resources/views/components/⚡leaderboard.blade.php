<?php

use App\Models\LeaderboardEntry;
use App\Models\Movie;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NetworkStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Leaderboard — Quiz App')] class extends Component
{
    public string $movieFilter = '';

    public function mount(): void
    {
        if ($this->isOnline) {
            app(LeaderboardService::class)->syncPending();
        }
    }

    #[Computed]
    public function isOnline(): bool
    {
        return app(NetworkStatus::class)->isOnline();
    }

    #[Computed]
    public function connectionType(): string
    {
        return app(NetworkStatus::class)->getConnectionType();
    }

    #[Computed]
    public function deviceId(): string
    {
        return app(DeviceIdentity::class)->getDeviceId();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, LeaderboardEntry> */
    #[Computed]
    public function entries(): \Illuminate\Database\Eloquent\Collection
    {
        $query = LeaderboardEntry::query()->orderByDesc('score')->limit(50);

        if ($this->movieFilter !== '') {
            $query->forMovie($this->movieFilter);
        }

        return $query->get();
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    #[Computed]
    public function movieSlugs(): \Illuminate\Support\Collection
    {
        return LeaderboardEntry::query()
            ->select('movie_slug')
            ->distinct()
            ->orderBy('movie_slug')
            ->pluck('movie_slug');
    }

    public function filterByMovie(string $slug): void
    {
        $this->movieFilter = $this->movieFilter === $slug ? '' : $slug;
        unset($this->entries);
    }

    public function clearFilter(): void
    {
        $this->movieFilter = '';
        unset($this->entries);
    }
};
?>

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Leaderboard</h1>
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Home</a>
        </div>

        @if ($this->isOnline)
            {{-- Movie filter tabs --}}
            @if ($this->movieSlugs->isNotEmpty())
                <div class="mb-4 flex flex-wrap gap-2">
                    <button
                        wire:click="clearFilter"
                        @class([
                            'rounded-full px-4 py-2 text-sm font-semibold transition-all duration-200',
                            'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-md' => $movieFilter === '',
                            'bg-white/60 text-gray-600 border-2 border-white hover:border-ocean-300' => $movieFilter !== '',
                        ])
                    >
                        All
                    </button>
                    @foreach ($this->movieSlugs as $slug)
                        <button
                            wire:key="filter-{{ $slug }}"
                            wire:click="filterByMovie('{{ $slug }}')"
                            @class([
                                'rounded-full px-4 py-2 text-sm font-semibold transition-all duration-200',
                                'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-md' => $movieFilter === $slug,
                                'bg-white/60 text-gray-600 border-2 border-white hover:border-ocean-300' => $movieFilter !== $slug,
                            ])
                        >
                            {{ str_replace('-', ' ', ucwords($slug, '-')) }}
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Scores list --}}
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6">
                @if ($this->entries->isEmpty())
                    <p class="text-center text-gray-500">No scores yet. Play a quiz to get on the board!</p>
                @else
                    <div class="space-y-3">
                        @foreach ($this->entries as $index => $entry)
                            <div
                                wire:key="entry-{{ $entry->id }}"
                                @class([
                                    'flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200',
                                    'bg-ocean-50 border border-ocean-200' => $entry->device_id === $this->deviceId,
                                    'bg-white/60' => $entry->device_id !== $this->deviceId,
                                ])
                            >
                                {{-- Rank --}}
                                <span @class([
                                    'flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold',
                                    'bg-gradient-to-r from-yellow-400 to-amber-400 text-white' => $index === 0,
                                    'bg-gradient-to-r from-gray-300 to-gray-400 text-white' => $index === 1,
                                    'bg-gradient-to-r from-orange-300 to-orange-400 text-white' => $index === 2,
                                    'bg-gray-100 text-gray-500' => $index > 2,
                                ])>
                                    {{ $index + 1 }}
                                </span>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-800 truncate">
                                        {{ $entry->username }}
                                        @if ($entry->device_id === $this->deviceId)
                                            <span class="text-ocean-500">(You)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ str_replace('-', ' ', ucwords($entry->movie_slug, '-')) }}</p>
                                </div>

                                {{-- Score --}}
                                <span class="text-sm font-bold text-ocean-600">{{ $entry->score }}/{{ $entry->total }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-8 text-center" data-offline-message>
                <div class="mb-4 text-4xl">
                    📡
                </div>
                <h2 class="text-lg font-bold text-gray-700 mb-2">You're Offline</h2>
                <p class="text-sm text-gray-500">The leaderboard requires an internet connection. Please connect to Wi-Fi or mobile data to view scores.</p>
            </div>
        @endif
    </div>
</div>
