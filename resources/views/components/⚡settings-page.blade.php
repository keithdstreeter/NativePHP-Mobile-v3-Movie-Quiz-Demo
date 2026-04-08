<?php

use App\Models\AgeGroup;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\UserSetting;
use App\Services\AvatarService;
use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NativeFeedback;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Native\Mobile\Attributes\OnNative;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;
use Native\Mobile\Facades\Camera;
use Native\Mobile\Events\Gallery\MediaSelected;
use Native\Mobile\Events\Camera\PhotoTaken;

new #[Title('Settings — Quiz App')] class extends Component
{
    public bool $gateUnlocked = true;

    public ?string $avatarUrl = null;

    public ?string $avatarError = null;

    public ?int $selectedAgeGroupId = null;

    public bool $soundEnabled = true;

    public bool $hapticsEnabled = true;

    public bool $showResetConfirm = false;

    public bool $resetComplete = false;

    #[Validate('required|alpha_num|min:3|max:20')]
    public string $username = '';

    public bool $usernameSaved = false;

    /** @var array{model: string, os: string, platform: string} */
    public array $deviceInfo = [];

    public function mount(): void
    {
        $this->avatarUrl = app(AvatarService::class)->avatarUrl();

        $savedId = UserSetting::get('age_group_id');

        if ($savedId) {
            $this->selectedAgeGroupId = (int) $savedId;
        }

        $this->soundEnabled = UserSetting::get('sound_enabled') !== '0';
        $this->hapticsEnabled = UserSetting::get('haptics_enabled') !== '0';

        $identity = app(DeviceIdentity::class);
        $this->username = $identity->getUsername();
        $this->deviceInfo = $identity->getDeviceInfo();
    }

    #[On('parent-gate-passed')]
    public function unlockGate(): void
    {
        $this->gateUnlocked = true;
    }

    public function changeAgeGroup(int $ageGroupId): void
    {
        $this->selectedAgeGroupId = $ageGroupId;
        UserSetting::set('age_group_id', (string) $ageGroupId);
    }

    public function toggleSound(): void
    {
        $this->soundEnabled = ! $this->soundEnabled;
        UserSetting::set('sound_enabled', $this->soundEnabled ? '1' : '0');
    }

    public function toggleHaptics(): void
    {
        $this->hapticsEnabled = ! $this->hapticsEnabled;
        UserSetting::set('haptics_enabled', $this->hapticsEnabled ? '1' : '0');
    }

    public function updateUsername(): void
    {
        $this->validate();

        $identity = app(DeviceIdentity::class);
        $identity->setUsername($this->username);

        app(LeaderboardService::class)->syncUsername(
            $identity->getDeviceId(),
            $this->username,
        );

        $this->usernameSaved = true;
    }

    public function confirmReset(): void
    {
        if (function_exists('nativephp_call')) {
            Dialog::alert(
                'Reset Progress',
                'This will delete all quiz history and cannot be undone.',
                ['Cancel', 'Yes, Reset']
            )->id('reset-progress')->remember()->show();

            return;
        }

        $this->showResetConfirm = true;
    }

    #[On('native:' . ButtonPressed::class)]
    public function handleAlertButton(int $index, string $label, ?string $id = null): void
    {
        if ($id === 'reset-progress' && $index === 1) {
            $this->resetProgress();
        }
    }

    public function cancelReset(): void
    {
        $this->showResetConfirm = false;
    }

    public function resetProgress(): void
    {
        QuizAnswer::query()->delete();
        QuizSession::query()->delete();
        $this->showResetConfirm = false;
        $this->resetComplete = true;
    }

    public function logout(): void
    {
        app(AvatarService::class)->clearLocal();
        session()->forget(['auth_token', 'token_verified_at']);
        $this->redirect(route('login'), navigate: true);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AgeGroup> */
    #[Computed]
    public function ageGroups(): \Illuminate\Database\Eloquent\Collection
    {
        return AgeGroup::active()->orderBy('sort_order')->get();
    }

    public function takeAvatarPhoto(): void
    {
        Camera::getPhoto();
    }

    #[OnNative(PhotoTaken::class)]
    public function handlePhotoTaken(string $path): void
    {
        $this->processAvatarFromPath($path);
    }

    public function pickAvatarFromGallery(): void
    {
        Camera::pickImages('images');
    }

    #[OnNative(MediaSelected::class)]
    public function handleMediaSelected(bool $success = false, array $files = [], int $count = 0): void
    {
        if (empty($files)) {
            return;
        }

        $this->processAvatarFromPath($files[0]['path']);
    }

    protected function processAvatarFromPath(string $path): void
    {
        $service = app(AvatarService::class);
        $rawUrl = $service->saveRawTemporary($path);

        $this->dispatch('compress-avatar', url: $rawUrl);
    }

    public function saveCompressedAvatar(string $data): void
    {
        $service = app(AvatarService::class);

        $error = $service->saveFromBase64($data);

        if ($error) {
            $service->clearLocal();
            $this->avatarUrl = null;
            $this->avatarError = $error;

            return;
        }

        $this->avatarError = null;
        $this->avatarUrl = $service->avatarUrl();
        Dialog::toast('Avatar updated');
    }

    public function saveRawAvatarFallback(): void
    {
        $service = app(AvatarService::class);

        $rawPath = Storage::path('avatar_raw.jpg');

        if (! file_exists($rawPath)) {
            return;
        }

        $error = $service->saveFromPath($rawPath);

        if ($error) {
            $this->avatarError = $error;

            return;
        }

        $this->avatarError = null;
        $this->avatarUrl = $service->avatarUrl();

        Dialog::toast('Avatar updated');
    }
};
?>

<div class="min-h-screen px-4 py-8">
    <div class="mx-auto max-w-lg">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
            <a href="{{ route('home') }}" wire:navigate class="text-sm font-medium text-ocean-500 hover:text-ocean-600 transition-colors">&larr; Home</a>
        </div>

        @if (! $gateUnlocked)
            <livewire:parent-gate />
        @else
                    <div class="space-y-6">
                        {{-- Age Group --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up" style="animation-delay: 0s">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Age Group</h2>
                            <div class="grid gap-3">
                                @foreach ($this->ageGroups as $ageGroup)
                                    <button
                                        wire:key="age-group-{{ $ageGroup->id }}"
                                        wire:click="changeAgeGroup({{ $ageGroup->id }})"
                                        x-data="{ pressed: false }"
                                        x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                                        :class="pressed ? 'scale-95' : 'scale-100'"
                                        @class([
                                            'w-full rounded-2xl px-5 py-4 text-base font-bold transition-all duration-200 min-h-[52px]',
                                            'bg-gradient-to-r from-ocean-500 to-candy-500 text-white shadow-lg shadow-ocean-200' => $selectedAgeGroupId === $ageGroup->id,
                                            'bg-white/60 text-gray-700 border-2 border-white hover:border-ocean-300' => $selectedAgeGroupId !== $ageGroup->id,
                                        ])
                                    >
                                        {{ $ageGroup->label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Avatar --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up"
                            style="animation-delay: 0.1s"
                            x-data
                            @compress-avatar.window="
                                const img = new Image();
                                img.onload = () => {
                                    const maxSize = 400;
                                    let w = img.width, h = img.height;
                                    if (w > maxSize || h > maxSize) {
                                        const ratio = Math.min(maxSize / w, maxSize / h);
                                        w = Math.round(w * ratio);
                                        h = Math.round(h * ratio);
                                    }
                                    const canvas = document.createElement('canvas');
                                    canvas.width = w;
                                    canvas.height = h;
                                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                                    const base64 = canvas.toDataURL('image/jpeg', 0.75).split(',')[1];
                                    $wire.saveCompressedAvatar(base64);
                                };
                                img.onerror = () => $wire.saveRawAvatarFallback();
                                img.src = $event.detail.url;
                            ">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Profile Photo</h2>
                            <div class="flex items-center gap-5">
                                <div class="shrink-0">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="Avatar"
                                            class="h-20 w-20 rounded-full object-cover border-2 border-white shadow-md" />
                                    @else
                                        <div
                                            class="h-20 w-20 rounded-full bg-linear-to-br from-ocean-300 to-candy-300 flex items-center justify-center border-2 border-white shadow-md">
                                            <span class="text-2xl font-bold text-white">{{ strtoupper(substr($username ?: 'U', 0, 1)) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-2 flex-1">
                                    <button wire:click="takeAvatarPhoto"
                                        class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-4 py-3 text-sm font-bold text-white transition-all duration-200 min-h-[44px]">
                                        Take Photo
                                    </button>
                                    <button wire:click="pickAvatarFromGallery"
                                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-sm font-bold text-gray-700 hover:border-ocean-300 transition-all duration-200 min-h-[44px]">
                                        Choose from Gallery
                                    </button>
                                </div>
                            </div>
                            @if ($avatarError)
                                <p class="mt-3 text-sm text-candy-500 w-full">{{ $avatarError }}</p>
                            @endif
                        </div>

                        {{-- Username --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up" style="animation-delay: 0.1s">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Username</h2>
                            <form wire:submit="updateUsername" class="space-y-3">
                                <div>
                                    <input
                                        type="text"
                                        wire:model="username"
                                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors"
                                        placeholder="Enter username"
                                        maxlength="20"
                                    />
                                    @error('username')
                                        <p class="mt-1 text-sm text-candy-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button
                                    type="submit"
                                    class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-4 py-3 text-sm font-bold text-white transition-all duration-200 min-h-[44px]"
                                >
                                    Save Username
                                </button>
                                @if ($usernameSaved)
                                    <p class="text-sm text-mint-500 font-semibold animate-fade-in">Username saved!</p>
                                @endif
                            </form>
                        </div>

                        {{-- Sound & Haptics --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up" style="animation-delay: 0.2s">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Preferences</h2>
                            <div class="space-y-5">
                                <div class="flex items-center justify-between min-h-[44px]">
                                    <span class="text-base text-gray-700">Sound Effects</span>
                                    <button
                                        wire:click="toggleSound"
                                        @class([
                                            'relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200',
                                            'bg-ocean-500' => $soundEnabled,
                                            'bg-gray-300' => !$soundEnabled,
                                        ])
                                    >
                                        <span @class([
                                            'inline-block h-6 w-6 rounded-full bg-white shadow-sm transition-transform duration-200',
                                            'translate-x-7' => $soundEnabled,
                                            'translate-x-1' => !$soundEnabled,
                                        ])></span>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between min-h-[44px]">
                                    <span class="text-base text-gray-700">Haptic Feedback</span>
                                    <button
                                        wire:click="toggleHaptics"
                                        @class([
                                            'relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-200',
                                            'bg-ocean-500' => $hapticsEnabled,
                                            'bg-gray-300' => !$hapticsEnabled,
                                        ])
                                    >
                                        <span @class([
                                            'inline-block h-6 w-6 rounded-full bg-white shadow-sm transition-transform duration-200',
                                            'translate-x-7' => $hapticsEnabled,
                                            'translate-x-1' => !$hapticsEnabled,
                                        ])></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Reset Progress --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up" style="animation-delay: 0.3s">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Data</h2>

                            @if ($resetComplete)
                                <p class="text-sm text-mint-500 font-semibold mb-3 animate-fade-in">All progress has been reset.</p>
                            @endif

                            @if ($showResetConfirm)
                                <p class="text-sm text-gray-600 mb-3">Are you sure? This will delete all quiz history and cannot be undone.</p>
                                <div class="flex gap-3">
                                    <button
                                        wire:click="resetProgress"
                                        class="flex-1 rounded-2xl bg-candy-500 px-4 py-4 text-sm font-bold text-white hover:bg-candy-600 transition-all duration-200 min-h-[48px]"
                                    >
                                        Yes, Reset
                                    </button>
                                    <button
                                        wire:click="cancelReset"
                                        class="flex-1 rounded-2xl border-2 border-white bg-white/60 px-4 py-4 text-sm font-bold text-gray-600 hover:border-gray-300 transition-all duration-200 min-h-[48px]"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <button
                                    wire:click="confirmReset"
                                    class="w-full rounded-2xl border-2 border-candy-200 px-4 py-4 text-sm font-bold text-candy-500 hover:bg-candy-50 transition-all duration-200 min-h-[48px]"
                                >
                                    Reset All Progress
                                </button>
                            @endif
                        </div>

                        {{-- Device Info --}}
                        <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up" style="animation-delay: 0.4s">
                            <h2 class="text-lg font-semibold text-gray-700 mb-4">Device Info</h2>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between min-h-[36px]">
                                    <span class="text-sm text-gray-500">Model</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['model'] }}</span>
                                </div>
                                <div class="flex items-center justify-between min-h-[36px]">
                                    <span class="text-sm text-gray-500">OS</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['os'] }}</span>
                                </div>
                                <div class="flex items-center justify-between min-h-[36px]">
                                    <span class="text-sm text-gray-500">Platform</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $deviceInfo['platform'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
        @endif

        {{-- Account --}}
        <div class="mt-6 rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 animate-fade-in-up">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Account</h2>
            <button
                wire:click="logout"
                class="w-full rounded-2xl border-2 border-candy-200 px-4 py-4 text-sm font-bold text-candy-500 hover:bg-candy-50 transition-all duration-200 min-h-[48px]"
            >
                Log Out
            </button>
        </div>
    </div>
</div>
