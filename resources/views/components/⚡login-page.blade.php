<?php

use App\Services\DeviceIdentity;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Login')] class extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public string $error = '';

    public function mount(): void
    {
        if (session('auth_token')) {
            $this->redirect(route('home'), navigate: true);
        }
    }

    public function login(DeviceIdentity $deviceIdentity): void
    {
        $this->error = '';

        $this->validate();

        $deviceInfo = $deviceIdentity->getDeviceInfo();

        try {
            $response = Http::api()->post('/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
                'device_name' => $deviceInfo['model'],
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            $this->error = 'Unable to connect. Please check your connection.';

            return;
        }

        if ($response->successful() && $response->json('token')) {
            session(['auth_token' => $response->json('token')]);
            $this->redirect(route('home'), navigate: true);

            return;
        }

        $this->error = $response->json('message') ?? 'Invalid credentials. Please try again.';
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
            <h1 class="text-4xl font-bold bg-gradient-to-r from-ocean-500 to-candy-500 bg-clip-text text-transparent mb-2">
                Welcome Back
            </h1>
            <p class="text-base text-gray-500">Sign in to your account</p>
        </div>

        <form wire:submit="login">
            <div class="rounded-2xl bg-white/80 backdrop-blur-sm border-2 border-white p-6 space-y-4" style="animation: fade-in-up 0.4s ease-out 0.1s both">
                @if ($error)
                    <div class="rounded-xl bg-candy-50 border border-candy-200 px-4 py-3 text-sm font-medium text-candy-600 animate-wiggle">
                        {{ $error }}
                    </div>
                @endif

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
                        autocomplete="current-password"
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
                        class="w-full rounded-2xl bg-gradient-to-r from-ocean-500 to-candy-500 px-6 py-5 text-lg font-bold text-white shadow-lg shadow-ocean-200 hover:shadow-xl transition-all duration-200 min-h-[56px]"
                    >
                        <span wire:loading.remove wire:target="login">Login</span>
                        <span wire:loading wire:target="login">Signing in…</span>
                    </button>
                </div>

                <div class="flex items-center gap-3 py-1">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-sm text-gray-400 font-medium">or</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <button
                    type="button"
                    x-data="{ pressed: false }"
                    x-on:click="pressed = true; setTimeout(() => pressed = false, 300)"
                    :class="pressed ? 'scale-95' : 'scale-100'"
                    class="w-full flex items-center justify-center gap-3 rounded-2xl border-2 border-ocean-200 bg-white/80 px-6 py-4 text-base font-bold text-gray-700 hover:border-ocean-300 hover:shadow-md transition-all duration-200 min-h-[56px]"
                >
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Login with Google
                </button>
            </div>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6" style="animation: fade-in-up 0.4s ease-out 0.2s both">
            Don't have an account?
            <a href="{{ route('register') }}" wire:navigate class="font-bold text-ocean-500 hover:text-ocean-600 transition-colors">
                Register
            </a>
        </p>
    </div>
</div>
