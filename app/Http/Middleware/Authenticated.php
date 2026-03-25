<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class Authenticated
{
    private const VERIFICATION_INTERVAL_MINUTES = 15;

    private const OFFLINE_GRACE_PERIOD_HOURS = 24;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = session('auth_token');

        if (! $token) {
            return redirect()->route('login');
        }

        if ($this->tokenNeedsVerification()) {
            $result = $this->verifyToken($token);

            if ($result === false) {
                $this->clearAuthSession();

                return redirect()->route('login');
            }
        }

        return $next($request);
    }

    private function tokenNeedsVerification(): bool
    {
        $verifiedAt = session('token_verified_at');

        return ! $verifiedAt || now()->diffInMinutes($verifiedAt) >= self::VERIFICATION_INTERVAL_MINUTES;
    }

    /**
     * Verify the token against the API.
     *
     * @return bool|null true = valid, false = invalid/expired, null = network error (grace period applies)
     */
    private function verifyToken(string $token): ?bool
    {
        try {
            $response = Http::api()
                ->withToken($token)
                ->get('/auth/me');

            if ($response->successful()) {
                session(['token_verified_at' => now()]);

                return true;
            }

            return false;
        } catch (ConnectionException) {
            $verifiedAt = session('token_verified_at');

            if (! $verifiedAt || now()->diffInHours($verifiedAt) >= self::OFFLINE_GRACE_PERIOD_HOURS) {
                return false;
            }

            return null;
        }
    }

    private function clearAuthSession(): void
    {
        session()->forget(['auth_token', 'token_verified_at']);
    }
}
