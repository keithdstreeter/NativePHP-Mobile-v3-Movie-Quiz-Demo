<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Native\Mobile\Facades\Browser;

use function Pest\Laravel\get;

it('stores token in session and redirects to home on callback with token', function () {
    get('/auth/callback?token=abc123')
        ->assertRedirectToRoute('home');

    expect(session('auth_token'))->toBe('abc123');
    expect(session('token_verified_at'))->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('redirects to login on callback without token', function () {
    get('/auth/callback')
        ->assertRedirectToRoute('login');
});

it('loginWithGoogle calls the redirect endpoint and opens the browser', function () {
    Http::fake([
        '*/auth/google/redirect' => Http::response(['url' => 'https://accounts.google.com/o/oauth2/auth?foo=bar'], 200),
    ]);

    Browser::shouldReceive('auth')
        ->once()
        ->with('https://accounts.google.com/o/oauth2/auth?foo=bar');

    Livewire::test('login-page')
        ->call('loginWithGoogle')
        ->assertHasNoErrors()
        ->assertSet('error', '');
});

it('loginWithGoogle sets error when url is missing from response', function () {
    Http::fake([
        '*/auth/google/redirect' => Http::response([], 200),
    ]);

    Browser::shouldReceive('auth')->never();

    Livewire::test('login-page')
        ->call('loginWithGoogle')
        ->assertSet('error', 'Unable to start Google login. Please try again.');
});

it('loginWithGoogle sets error on connection failure', function () {
    Http::fake([
        '*/auth/google/redirect' => fn () => throw new ConnectionException,
    ]);

    Browser::shouldReceive('auth')->never();

    Livewire::test('login-page')
        ->call('loginWithGoogle')
        ->assertSet('error', 'Unable to connect. Please check your connection.');
});
