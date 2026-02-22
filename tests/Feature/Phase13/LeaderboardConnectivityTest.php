<?php

use App\Services\NetworkStatus;
use Livewire\Livewire;

it('leaderboard page renders successfully', function () {
    $this->get(route('leaderboard'))
        ->assertSuccessful();
});

it('leaderboard shows content when online', function () {
    Livewire::test('leaderboard')
        ->assertSee('Leaderboard')
        ->assertDontSee('Offline');
});

it('leaderboard shows offline message when not connected', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(false);
        $mock->shouldReceive('getConnectionType')->andReturn('none');
    });

    Livewire::test('leaderboard')
        ->assertSee('Offline')
        ->assertSee('requires an internet connection');
});

it('leaderboard is accessible when connected', function () {
    $this->mock(NetworkStatus::class, function ($mock) {
        $mock->shouldReceive('isOnline')->andReturn(true);
        $mock->shouldReceive('getConnectionType')->andReturn('wifi');
    });

    Livewire::test('leaderboard')
        ->assertDontSee('requires an internet connection')
        ->assertSee('Leaderboard');
});

it('is registered as a singleton in the service container', function () {
    $instance1 = app(NetworkStatus::class);
    $instance2 = app(NetworkStatus::class);

    expect($instance1)->toBe($instance2);
});
