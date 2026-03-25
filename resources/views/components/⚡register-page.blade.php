<?php

use App\Services\DeviceIdentity;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Register')] class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public string $error = '';

    public function mount(): void
    {
        if (session('auth_token')) {
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function register(DeviceIdentity $deviceIdentity): void
    {
        $this->error = '';

        $this->validate();

        $deviceInfo = $deviceIdentity->getDeviceInfo();

        try {
            $response = Http::api()->post('/auth/register', [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'device_name' => $deviceInfo['model'],
            ]);
        } catch (ConnectionException) {
            $this->error = 'Unable to connect. Please check your connection.';

            return;
        }

        if ($response->successful() && $response->json('token')) {
            session(['auth_token' => $response->json('token'), 'token_verified_at' => now()]);
            $this->redirect(route('home'), navigate: true);

            return;
        }

        $this->error = $response->json('message') ?? 'Registration failed. Please try again.';
    }
};
?>

<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg" x-data="{ shown: false }" x-init="$nextTick(() => shown = true)">
        <div
            class="text-center mb-8"
            x-show="shown"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <h1 class="text-4xl font-bold bg-gradient-to-r from-candy-500 to-ocean-500 bg-clip-text text-transparent mb-2">
                Create Account
            </h1>
            <p class="text-base text-gray-500">Join and start playing today</p>
        </div>

        <form wire:submit.prevent="register">
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 space-y-4" style="animation: fade-in-up 0.4s ease-out 0.1s both">
                @if ($error)
                    <div class="rounded-xl bg-candy-50 border border-candy-200 px-4 py-3 text-sm font-medium text-candy-600 animate-wiggle">
                        {{ $error }}
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Name</label>
                    <input
                        wire:model="name"
                        type="text"
                        placeholder="Your name"
                        autocomplete="name"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors"
                    />
                    @error('name') <p class="text-sm text-candy-600 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Email</label>
                    <input
                        wire:model="email"
                        type="email"
                        placeholder="you@example.com"
                        autocomplete="email"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors"
                    />
                    @error('email') <p class="text-sm text-candy-600 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-600 pl-1">Password</label>
                    <input
                        wire:model="password"
                        type="password"
                        placeholder="••••••••"
                        autocomplete="new-password"
                        class="w-full rounded-2xl border-2 border-white bg-white/60 px-4 py-3 text-base text-gray-700 focus:border-ocean-300 focus:outline-none transition-colors"
                    />
                    @error('password') <p class="text-sm text-candy-600 pl-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        x-data="{ pressed: false }"
                        x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                        :class="pressed ? 'scale-95' : 'scale-100'"
                        class="w-full rounded-2xl bg-gradient-to-r from-candy-500 to-ocean-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-candy-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                    >
                        <span wire:loading.remove wire:target="register">Create Account</span>
                        <span wire:loading wire:target="register">Creating account…</span>
                    </button>
                </div>
            </div>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6" style="animation: fade-in-up 0.4s ease-out 0.2s both">
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate class="font-bold text-ocean-500 hover:text-ocean-600 transition-colors">
                Login
            </a>
        </p>
    </div>
</div>
