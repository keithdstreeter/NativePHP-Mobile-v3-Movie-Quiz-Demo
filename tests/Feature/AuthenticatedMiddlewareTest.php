<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\get;

it('redirects to login when no token in session', function () {
    get(route('home'))
        ->assertRedirectToRoute('login');
});

it('allows access when token is valid and recently verified', function () {
    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now(),
    ]);

    get(route('home'))
        ->assertOk();
});

it('verifies token with API when verification has expired', function () {
    Http::fake([
        '*/auth/me' => Http::response(['id' => 1, 'name' => 'Test'], 200),
    ]);

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now()->subMinutes(16),
    ]);

    get(route('home'))
        ->assertOk();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/auth/me')
        && $request->hasHeader('Authorization', 'Bearer valid-token')
    );

    expect(session('token_verified_at'))->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('redirects to login when API returns unauthorized', function () {
    Http::fake([
        '*/auth/me' => Http::response(['message' => 'Unauthenticated'], 401),
    ]);

    session([
        'auth_token' => 'expired-token',
        'token_verified_at' => now()->subMinutes(20),
    ]);

    get(route('home'))
        ->assertRedirectToRoute('login');

    expect(session('auth_token'))->toBeNull();
    expect(session('token_verified_at'))->toBeNull();
});

it('allows access during network error within grace period', function () {
    Http::fake([
        '*/auth/me' => fn () => throw new ConnectionException,
    ]);

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now()->subMinutes(20),
    ]);

    get(route('home'))
        ->assertOk();
});

it('redirects to login during network error beyond grace period', function () {
    Http::fake([
        '*/auth/me' => fn () => throw new ConnectionException,
    ]);

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now()->subHours(25),
    ]);

    get(route('home'))
        ->assertRedirectToRoute('login');

    expect(session('auth_token'))->toBeNull();
});

it('redirects to login during network error when never verified', function () {
    Http::fake([
        '*/auth/me' => fn () => throw new ConnectionException,
    ]);

    session([
        'auth_token' => 'valid-token',
    ]);

    get(route('home'))
        ->assertRedirectToRoute('login');
});

it('verifies token on first request when token_verified_at is missing', function () {
    Http::fake([
        '*/auth/me' => Http::response(['id' => 1], 200),
    ]);

    session([
        'auth_token' => 'valid-token',
    ]);

    get(route('home'))
        ->assertOk();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/auth/me'));
});

it('skips API call when token was recently verified', function () {
    Http::fake([
        '*/auth/me' => Http::response(['id' => 1], 200),
    ]);

    session([
        'auth_token' => 'valid-token',
        'token_verified_at' => now()->subMinutes(5),
    ]);

    get(route('home'))
        ->assertOk();

    Http::assertNothingSent();
});
