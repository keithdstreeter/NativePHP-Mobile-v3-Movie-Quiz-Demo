<?php

namespace App\Providers;

use App\Services\DeviceIdentity;
use App\Services\LeaderboardService;
use App\Services\NativeFeedback;
use App\Services\NetworkStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DeviceIdentity::class);
        $this->app->singleton(LeaderboardService::class);
        $this->app->singleton(NativeFeedback::class);
        $this->app->singleton(NetworkStatus::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::macro('api', fn () => Http::baseUrl(config('services.api.url'))
            ->timeout(10)
            ->acceptJson()
        );
    }
}
